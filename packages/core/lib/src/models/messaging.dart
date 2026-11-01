import 'package:freezed_annotation/freezed_annotation.dart';

import 'common.dart';

part 'messaging.freezed.dart';
part 'messaging.g.dart';

@freezed
abstract class Participant with _$Participant {
  const factory Participant({required String id, required String name, String? avatarUrl, String? role}) = _Participant;

  factory Participant.fromJson(Map<String, dynamic> json) => _$ParticipantFromJson(json);
}

@freezed
abstract class MessageAttachment with _$MessageAttachment {
  const factory MessageAttachment({
    required String id,
    String? mime,
    int? size,
    String? url,
    int? durationMs,
    List<double>? waveform,
  }) = _MessageAttachment;

  factory MessageAttachment.fromJson(Map<String, dynamic> json) => _$MessageAttachmentFromJson(json);
}

@freezed
abstract class MessageReplyTo with _$MessageReplyTo {
  const factory MessageReplyTo({required String id, String? body, String? senderName}) = _MessageReplyTo;

  factory MessageReplyTo.fromJson(Map<String, dynamic> json) => _$MessageReplyToFromJson(json);
}

@freezed
abstract class MessageReaction with _$MessageReaction {
  const factory MessageReaction({required String emoji, @Default(1) int count, @Default(false) bool mine}) =
      _MessageReaction;

  factory MessageReaction.fromJson(Map<String, dynamic> json) => _$MessageReactionFromJson(json);
}

/// **Message**.
@freezed
abstract class Message with _$Message {
  const factory Message({
    required String id,
    required String conversationId,
    PersonRef? sender,
    @Default('text') String type,
    String? body,
    @Default(<String, dynamic>{}) Map<String, dynamic> meta,
    @Default(<MessageAttachment>[]) List<MessageAttachment> attachments,
    MessageReplyTo? replyTo,
    @Default(<MessageReaction>[]) List<MessageReaction> reactions,
    String? channel,
    DateTime? editedAt,
    required DateTime createdAt,
    @Default(false) bool deleted,
  }) = _Message;

  factory Message.fromJson(Map<String, dynamic> json) => _$MessageFromJson(json);
}

/// **Conversation**.
@freezed
abstract class Conversation with _$Conversation {
  const factory Conversation({
    required String id,
    @Default('direct') String type,
    String? title,
    @Default(<Participant>[]) List<Participant> participants,
    Message? lastMessage,
    @Default(0) int unreadCount,
    @Default(false) bool isMuted,
    DateTime? updatedAt,
  }) = _Conversation;

  factory Conversation.fromJson(Map<String, dynamic> json) => _$ConversationFromJson(json);
}
