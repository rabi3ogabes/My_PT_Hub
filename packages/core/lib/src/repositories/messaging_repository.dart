import 'dart:convert';

import 'package:dio/dio.dart';
import 'package:drift/drift.dart';

import '../models/common.dart';
import '../models/messaging.dart';
import '../models/sync.dart';
import '../network/api_client.dart';
import '../network/api_failure.dart';
import '../offline/database.dart';
import '../offline/sync_engine.dart';
import '../util/ulid.dart';
import 'base_repository.dart';

/// A message composed on this device that the server has not confirmed yet.
class OutgoingMessage {
  const OutgoingMessage({
    required this.id,
    required this.conversationId,
    required this.type,
    required this.createdAt,
    this.body,
    this.replyToId,
    this.filePath,
    this.durationMs,
    this.failed = false,
  });

  final String id;
  final String conversationId;
  final String type;
  final String? body;
  final String? replyToId;
  final String? filePath;
  final int? durationMs;
  final DateTime createdAt;
  final bool failed;
}

/// Conversations and messages with an offline compose queue.
///
/// Text messages travel through the sync queue (`entity: message`), media
/// messages (voice notes, images) are stored in `pending_uploads` and sent as
/// multipart when online. Both use a client ULID so retries are idempotent.
class MessagingRepository extends BaseRepository {
  MessagingRepository(super.api, super.db, this.sync);

  final SyncEngine sync;

  Future<Fetched<List<Conversation>>> conversations() => cachedGet(
        'conversations',
        '/conversations',
        (data) => BaseRepository.asList(data).map(Conversation.fromJson).toList(),
      );

  Future<CursorPage<Message>> messages(String conversationId, {String? cursor}) =>
      api.getPage('/conversations/$conversationId/messages', Message.fromJson, cursor: cursor);

  Future<void> markRead(String conversationId) => api.post('/conversations/$conversationId/read');

  Future<void> typing(String conversationId) => api.post('/conversations/$conversationId/typing');

  /// Queues a text message. Delivered as soon as the device is online.
  Future<OutgoingMessage> sendText(String conversationId, String body, {String? replyToId}) async {
    final id = Ulid.generate();
    final now = DateTime.now().toUtc();
    await sync.enqueue(
      entity: SyncEntity.message,
      op: SyncOp.upsert,
      payload: compactJson({
        'id': id,
        'conversation_id': conversationId,
        'type': 'text',
        'body': body,
        'reply_to_id': replyToId,
      }),
    );
    return OutgoingMessage(id: id, conversationId: conversationId, type: 'text', body: body, replyToId: replyToId, createdAt: now);
  }

  /// Queues a media message (voice note / image) and tries to upload it.
  Future<OutgoingMessage> sendMedia(
    String conversationId, {
    required String type,
    required String filePath,
    String? body,
    String? replyToId,
    int? durationMs,
  }) async {
    final id = Ulid.generate();
    final now = DateTime.now().toUtc();
    await db.into(db.pendingUploads).insert(
          PendingUploadsCompanion.insert(
            id: id,
            conversationId: conversationId,
            type: type,
            body: Value(body),
            replyToId: Value(replyToId),
            filePath: filePath,
            durationMs: Value(durationMs),
            createdAt: now,
          ),
        );
    await flushUploads();
    return OutgoingMessage(
      id: id,
      conversationId: conversationId,
      type: type,
      body: body,
      filePath: filePath,
      durationMs: durationMs,
      createdAt: now,
    );
  }

  /// Messages still waiting to be delivered for [conversationId].
  Future<List<OutgoingMessage>> outgoing(String conversationId) async {
    final queued = await (db.select(db.syncQueue)..where((t) => t.entity.equals(SyncEntity.message))).get();
    final texts = queued
        .map((e) => (entry: e, payload: jsonDecode(e.payload) as Map<String, dynamic>))
        .where((e) => e.payload['conversation_id'] == conversationId)
        .map((e) => OutgoingMessage(
              id: e.payload['id'].toString(),
              conversationId: conversationId,
              type: 'text',
              body: e.payload['body'] as String?,
              replyToId: e.payload['reply_to_id'] as String?,
              createdAt: e.entry.createdAt,
              failed: e.entry.status == 'rejected',
            ));
    final uploads = await (db.select(db.pendingUploads)..where((t) => t.conversationId.equals(conversationId))).get();
    final media = uploads.map((u) => OutgoingMessage(
          id: u.id,
          conversationId: conversationId,
          type: u.type,
          body: u.body,
          replyToId: u.replyToId,
          filePath: u.filePath,
          durationMs: u.durationMs,
          createdAt: u.createdAt,
          failed: u.attempts >= 5,
        ));
    return [...texts, ...media]..sort((a, b) => a.id.compareTo(b.id));
  }

  /// Uploads pending media messages. Stops at the first connectivity error.
  Future<void> flushUploads() async {
    final pending = await (db.select(db.pendingUploads)..orderBy([(t) => OrderingTerm.asc(t.id)])).get();
    for (final upload in pending) {
      try {
        final form = FormData.fromMap({
          'id': upload.id,
          'type': upload.type,
          'body': ?upload.body,
          'reply_to_id': ?upload.replyToId,
          'duration_ms': ?upload.durationMs,
          'file': await MultipartFile.fromFile(upload.filePath),
        });
        await api.upload('/conversations/${upload.conversationId}/messages', form);
        await (db.delete(db.pendingUploads)..where((t) => t.id.equals(upload.id))).go();
      } on ApiFailure catch (failure) {
        await (db.update(db.pendingUploads)..where((t) => t.id.equals(upload.id))).write(
          PendingUploadsCompanion(attempts: Value(upload.attempts + 1), lastError: Value(failure.code)),
        );
        if (failure.isConnectivity) return;
      }
    }
  }

  Future<void> discardOutgoing(String id) async {
    await (db.delete(db.pendingUploads)..where((t) => t.id.equals(id))).go();
    final queued = await (db.select(db.syncQueue)..where((t) => t.entity.equals(SyncEntity.message))).get();
    for (final entry in queued) {
      if ((jsonDecode(entry.payload) as Map<String, dynamic>)['id'] == id) {
        await sync.discardRejected(entry.id);
      }
    }
  }

  Future<Message> edit(String messageId, String body) async =>
      Message.fromJson((await api.patch('/messages/$messageId', data: {'body': body})).dataMap);

  Future<void> deleteMessage(String messageId) => api.delete('/messages/$messageId');

  Future<void> react(String messageId, String emoji) => api.post('/messages/$messageId/reactions', data: {'emoji': emoji});
}
