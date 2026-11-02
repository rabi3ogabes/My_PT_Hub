import 'package:aurum_core/aurum_core.dart';
import 'package:flutter/material.dart';
import 'package:flutter_riverpod/flutter_riverpod.dart';
import 'package:go_router/go_router.dart';

import '../../app/router.dart';
import '../../shared/l10n_ext.dart';
import '../../shared/widgets.dart';
import 'messages_providers.dart';

class ConversationsScreen extends ConsumerWidget {
  const ConversationsScreen({super.key});

  @override
  Widget build(BuildContext context, WidgetRef ref) {
    final l10n = context.l10n;
    final au = context.au;
    final me = ref.watch(authControllerProvider.select((s) => s.user?.id));
    final conversations = ref.watch(conversationsProvider);
    return AuPage(
      title: l10n.messages,
      onRefresh: () => ref.refresh(conversationsProvider.future),
      children: [
        conversations.when(
          loading: () => const PageSkeleton(),
          error: (e, _) => AuErrorState(error: e, onRetry: () => ref.invalidate(conversationsProvider)),
          data: (fetched) {
            final list = [...fetched.value]..sort((a, b) => (b.updatedAt ?? DateTime(0)).compareTo(a.updatedAt ?? DateTime(0)));
            if (list.isEmpty) {
              return AuEmptyState(icon: Icons.chat_bubble_outline_rounded, title: l10n.noMessagesTitle, message: l10n.noMessagesHint);
            }
            return Column(
              crossAxisAlignment: CrossAxisAlignment.stretch,
              children: [
                CachedNotice(visible: fetched.fromCache),
                AuCard(
                  padding: EdgeInsets.zero,
                  child: Column(
                    children: [
                      for (var i = 0; i < list.length; i++) ...[
                        _ConversationRow(conversation: list[i], meId: me),
                        if (i < list.length - 1) const Divider(indent: 72),
                      ],
                    ],
                  ),
                ),
              ],
            );
          },
        ),
        const SizedBox(height: AuSpacing.s4),
        Text(l10n.messagesOfflineNote, style: au.text.caption.copyWith(color: au.textMuted), textAlign: TextAlign.center),
      ],
    );
  }
}

class _ConversationRow extends StatelessWidget {
  const _ConversationRow({required this.conversation, required this.meId});

  final Conversation conversation;
  final String? meId;

  @override
  Widget build(BuildContext context) {
    final au = context.au;
    final l10n = context.l10n;
    final other = conversation.participants.where((p) => p.id != meId).firstOrNull;
    final title = conversation.title ?? other?.name ?? l10n.messages;
    final last = conversation.lastMessage;
    final preview = last == null
        ? l10n.sayHello
        : switch (last.type) {
            'voice' => '🎤 ${l10n.voiceNote}',
            'image' => '📷 ${l10n.photo}',
            'video' => '🎬 ${l10n.video}',
            _ => last.body ?? '',
          };
    final unread = conversation.unreadCount > 0;
    return AuListRow(
      leading: conversation.type == 'group' || conversation.type == 'broadcast'
          ? const AuIconTile(icon: Icons.groups_rounded, size: 48)
          : AuAvatar(name: title, imageUrl: other?.avatarUrl, size: 48, ring: unread),
      title: title,
      subtitle: preview,
      trailing: Column(
        crossAxisAlignment: CrossAxisAlignment.end,
        mainAxisSize: MainAxisSize.min,
        children: [
          if (conversation.updatedAt != null)
            Text(context.relativeDay(conversation.updatedAt!.toLocal()), style: au.text.caption.copyWith(color: unread ? au.accent : au.textMuted)),
          if (unread) ...[const SizedBox(height: 4), AuBadge.count(conversation.unreadCount)],
          if (conversation.isMuted) Icon(Icons.notifications_off_outlined, size: 14, color: au.textMuted),
        ],
      ),
      showChevron: false,
      onTap: () => context.push(Routes.thread(conversation.id)),
    );
  }
}
