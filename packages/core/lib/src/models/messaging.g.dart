// GENERATED CODE - DO NOT MODIFY BY HAND

part of 'messaging.dart';

// **************************************************************************
// JsonSerializableGenerator
// **************************************************************************

_Participant _$ParticipantFromJson(Map<String, dynamic> json) => _Participant(
  id: json['id'] as String,
  name: json['name'] as String,
  avatarUrl: json['avatar_url'] as String?,
  role: json['role'] as String?,
);

Map<String, dynamic> _$ParticipantToJson(_Participant instance) =>
    <String, dynamic>{
      'id': instance.id,
      'name': instance.name,
      'avatar_url': instance.avatarUrl,
      'role': instance.role,
    };

_MessageAttachment _$MessageAttachmentFromJson(Map<String, dynamic> json) =>
    _MessageAttachment(
      id: json['id'] as String,
      mime: json['mime'] as String?,
      size: (json['size'] as num?)?.toInt(),
      url: json['url'] as String?,
      durationMs: (json['duration_ms'] as num?)?.toInt(),
      waveform: (json['waveform'] as List<dynamic>?)
          ?.map((e) => (e as num).toDouble())
          .toList(),
    );

Map<String, dynamic> _$MessageAttachmentToJson(_MessageAttachment instance) =>
    <String, dynamic>{
      'id': instance.id,
      'mime': instance.mime,
      'size': instance.size,
      'url': instance.url,
      'duration_ms': instance.durationMs,
      'waveform': instance.waveform,
    };

_MessageReplyTo _$MessageReplyToFromJson(Map<String, dynamic> json) =>
    _MessageReplyTo(
      id: json['id'] as String,
      body: json['body'] as String?,
      senderName: json['sender_name'] as String?,
    );

Map<String, dynamic> _$MessageReplyToToJson(_MessageReplyTo instance) =>
    <String, dynamic>{
      'id': instance.id,
      'body': instance.body,
      'sender_name': instance.senderName,
    };

_MessageReaction _$MessageReactionFromJson(Map<String, dynamic> json) =>
    _MessageReaction(
      emoji: json['emoji'] as String,
      count: (json['count'] as num?)?.toInt() ?? 1,
      mine: json['mine'] as bool? ?? false,
    );

Map<String, dynamic> _$MessageReactionToJson(_MessageReaction instance) =>
    <String, dynamic>{
      'emoji': instance.emoji,
      'count': instance.count,
      'mine': instance.mine,
    };

_Message _$MessageFromJson(Map<String, dynamic> json) => _Message(
  id: json['id'] as String,
  conversationId: json['conversation_id'] as String,
  sender: json['sender'] == null
      ? null
      : PersonRef.fromJson(json['sender'] as Map<String, dynamic>),
  type: json['type'] as String? ?? 'text',
  body: json['body'] as String?,
  meta: json['meta'] as Map<String, dynamic>? ?? const <String, dynamic>{},
  attachments:
      (json['attachments'] as List<dynamic>?)
          ?.map((e) => MessageAttachment.fromJson(e as Map<String, dynamic>))
          .toList() ??
      const <MessageAttachment>[],
  replyTo: json['reply_to'] == null
      ? null
      : MessageReplyTo.fromJson(json['reply_to'] as Map<String, dynamic>),
  reactions:
      (json['reactions'] as List<dynamic>?)
          ?.map((e) => MessageReaction.fromJson(e as Map<String, dynamic>))
          .toList() ??
      const <MessageReaction>[],
  channel: json['channel'] as String?,
  editedAt: json['edited_at'] == null
      ? null
      : DateTime.parse(json['edited_at'] as String),
  createdAt: DateTime.parse(json['created_at'] as String),
  deleted: json['deleted'] as bool? ?? false,
);

Map<String, dynamic> _$MessageToJson(_Message instance) => <String, dynamic>{
  'id': instance.id,
  'conversation_id': instance.conversationId,
  'sender': instance.sender?.toJson(),
  'type': instance.type,
  'body': instance.body,
  'meta': instance.meta,
  'attachments': instance.attachments.map((e) => e.toJson()).toList(),
  'reply_to': instance.replyTo?.toJson(),
  'reactions': instance.reactions.map((e) => e.toJson()).toList(),
  'channel': instance.channel,
  'edited_at': instance.editedAt?.toIso8601String(),
  'created_at': instance.createdAt.toIso8601String(),
  'deleted': instance.deleted,
};

_Conversation _$ConversationFromJson(Map<String, dynamic> json) =>
    _Conversation(
      id: json['id'] as String,
      type: json['type'] as String? ?? 'direct',
      title: json['title'] as String?,
      participants:
          (json['participants'] as List<dynamic>?)
              ?.map((e) => Participant.fromJson(e as Map<String, dynamic>))
              .toList() ??
          const <Participant>[],
      lastMessage: json['last_message'] == null
          ? null
          : Message.fromJson(json['last_message'] as Map<String, dynamic>),
      unreadCount: (json['unread_count'] as num?)?.toInt() ?? 0,
      isMuted: json['is_muted'] as bool? ?? false,
      updatedAt: json['updated_at'] == null
          ? null
          : DateTime.parse(json['updated_at'] as String),
    );

Map<String, dynamic> _$ConversationToJson(_Conversation instance) =>
    <String, dynamic>{
      'id': instance.id,
      'type': instance.type,
      'title': instance.title,
      'participants': instance.participants.map((e) => e.toJson()).toList(),
      'last_message': instance.lastMessage?.toJson(),
      'unread_count': instance.unreadCount,
      'is_muted': instance.isMuted,
      'updated_at': instance.updatedAt?.toIso8601String(),
    };
