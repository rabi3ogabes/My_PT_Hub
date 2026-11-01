<?php

return [
    'preview' => [
        'voice' => '🎤 Voice note',
        'image' => '📷 Photo',
        'video' => '🎬 Video',
        'file' => '📎 File',
        'share' => '🔗 Shared item',
        'message' => 'New message',
    ],
    'errors' => [
        'self_conversation' => 'You cannot start a conversation with yourself.',
        'invalid_type' => 'This message type is not supported.',
        'id_conflict' => 'This message id is already used by another message.',
        'body_required' => 'Please write a message.',
        'body_too_long' => 'This message is too long.',
        'file_required' => 'Please attach a file.',
        'invalid_file' => 'This file type is not allowed for this message.',
        'invalid_reply' => 'The message you are replying to is not in this conversation.',
        'share_requires_kind' => 'Choose what you are sharing.',
        'not_owner' => 'You can only change your own messages.',
        'not_editable' => 'This message can no longer be changed.',
        'edit_window_expired' => 'Messages can only be edited within :minutes minutes of sending.',
        'conversation_not_found' => 'Conversation not found.',
        'message_not_found' => 'Message not found.',
        'attachment_not_found' => 'Attachment not found.',
        'not_a_member' => 'This person is not a member of your workspace.',
        'clients_message_staff_only' => 'You can message your coaching team only.',
        'clients_cannot_create_groups' => 'Only coaches can create group conversations.',
        'broadcast_forbidden' => 'You do not have permission to send broadcasts.',
        'broadcast_already_sent' => 'This broadcast has already been sent.',
    ],
];
