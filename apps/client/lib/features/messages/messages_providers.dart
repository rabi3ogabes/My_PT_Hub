import 'dart:async';

import 'package:aurum_core/aurum_core.dart';
import 'package:flutter_riverpod/flutter_riverpod.dart';

final conversationsProvider = FutureProvider.autoDispose<Fetched<List<Conversation>>>(
  (ref) => ref.watch(messagingRepositoryProvider).conversations(),
);

/// Total unread across conversations (bottom-nav badge).
final unreadMessagesProvider = Provider<int>((ref) {
  final list = ref.watch(conversationsProvider).value?.value ?? const <Conversation>[];
  return list.fold(0, (sum, c) => sum + c.unreadCount);
});

/// Thread state: server messages (newest first), outgoing queue, paging.
class ThreadState {
  const ThreadState({this.messages = const [], this.outgoing = const [], this.nextCursor, this.loadingMore = false});

  final List<Message> messages;
  final List<OutgoingMessage> outgoing;
  final String? nextCursor;
  final bool loadingMore;

  bool get hasMore => nextCursor != null;

  ThreadState copyWith({List<Message>? messages, List<OutgoingMessage>? outgoing, String? Function()? nextCursor, bool? loadingMore}) =>
      ThreadState(
        messages: messages ?? this.messages,
        outgoing: outgoing ?? this.outgoing,
        nextCursor: nextCursor != null ? nextCursor() : this.nextCursor,
        loadingMore: loadingMore ?? this.loadingMore,
      );
}

class ThreadController extends AsyncNotifier<ThreadState> {
  ThreadController(this.conversationId);

  final String conversationId;
  Timer? _poll;

  MessagingRepository get _repo => ref.read(messagingRepositoryProvider);

  @override
  Future<ThreadState> build() async {
    ref.onDispose(() => _poll?.cancel());
    // Lightweight refresh while the thread is open (Reverb sockets replace
    // this when the realtime channel is configured on the device).
    _poll = Timer.periodic(const Duration(seconds: 20), (_) => unawaited(refreshLatest()));
    final outgoing = await _repo.outgoing(conversationId);
    try {
      final page = await _repo.messages(conversationId);
      unawaited(_markRead());
      return ThreadState(messages: page.items, outgoing: outgoing, nextCursor: page.nextCursor);
    } on ApiFailure catch (f) {
      if (!f.isConnectivity) rethrow;
      return ThreadState(outgoing: outgoing);
    }
  }

  Future<void> _markRead() async {
    try {
      await _repo.markRead(conversationId);
      ref.invalidate(conversationsProvider);
    } on ApiFailure {
      // Read receipts are best effort.
    }
  }

  Future<void> refreshLatest() async {
    final current = state.value;
    if (current == null) return;
    try {
      final page = await _repo.messages(conversationId);
      final outgoing = await _repo.outgoing(conversationId);
      final known = page.items.map((m) => m.id).toSet();
      final older = current.messages.where((m) => !known.contains(m.id)).toList();
      state = AsyncData(current.copyWith(messages: [...page.items, ...older], outgoing: outgoing));
    } on ApiFailure {
      state = AsyncData(current.copyWith(outgoing: await _repo.outgoing(conversationId)));
    }
  }

  Future<void> loadMore() async {
    final current = state.value;
    if (current == null || !current.hasMore || current.loadingMore) return;
    state = AsyncData(current.copyWith(loadingMore: true));
    try {
      final page = await _repo.messages(conversationId, cursor: current.nextCursor);
      state = AsyncData(current.copyWith(messages: [...current.messages, ...page.items], nextCursor: () => page.nextCursor));
    } on ApiFailure {
      state = AsyncData(current.copyWith(loadingMore: false));
    }
  }

  Future<void> sendText(String body, {String? replyToId}) async {
    await _repo.sendText(conversationId, body, replyToId: replyToId);
    unawaited(ref.read(analyticsProvider).log(AnalyticsEvent.messageSent(type: 'text')));
    await _refreshOutgoing();
    // Give the queue a moment to flush, then pull the confirmed message.
    unawaited(Future<void>.delayed(const Duration(seconds: 2), refreshLatest));
  }

  Future<void> sendMedia({required String type, required String filePath, int? durationMs, String? replyToId}) async {
    await _repo.sendMedia(conversationId, type: type, filePath: filePath, durationMs: durationMs, replyToId: replyToId);
    unawaited(ref.read(analyticsProvider).log(AnalyticsEvent.messageSent(type: type)));
    await _refreshOutgoing();
    unawaited(Future<void>.delayed(const Duration(seconds: 2), refreshLatest));
  }

  Future<void> _refreshOutgoing() async {
    final current = state.value ?? const ThreadState();
    state = AsyncData(current.copyWith(outgoing: await _repo.outgoing(conversationId)));
  }

  Future<void> react(Message message, String emoji) async {
    final current = state.value;
    if (current == null) return;
    // Optimistic toggle.
    final reactions = [...message.reactions];
    final i = reactions.indexWhere((r) => r.emoji == emoji);
    if (i >= 0) {
      final r = reactions[i];
      final count = r.mine ? r.count - 1 : r.count + 1;
      if (count <= 0) {
        reactions.removeAt(i);
      } else {
        reactions[i] = r.copyWith(count: count, mine: !r.mine);
      }
    } else {
      reactions.add(MessageReaction(emoji: emoji, count: 1, mine: true));
    }
    state = AsyncData(current.copyWith(
      messages: [for (final m in current.messages) m.id == message.id ? m.copyWith(reactions: reactions) : m],
    ));
    AuHaptics.selection();
    try {
      await _repo.react(message.id, emoji);
    } on ApiFailure {
      await refreshLatest();
    }
  }

  Future<void> deleteMessage(Message message) async {
    await _repo.deleteMessage(message.id);
    await refreshLatest();
  }

  Future<void> discardOutgoing(OutgoingMessage m) async {
    await _repo.discardOutgoing(m.id);
    await _refreshOutgoing();
  }

  Future<void> typing() async {
    try {
      await _repo.typing(conversationId);
    } on ApiFailure {
      // Presence is best effort.
    }
  }
}

final threadProvider = AsyncNotifierProvider.autoDispose.family<ThreadController, ThreadState, String>(ThreadController.new);
