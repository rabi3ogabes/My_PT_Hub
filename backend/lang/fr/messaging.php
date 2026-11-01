<?php

return [
    'preview' => [
        'voice' => '🎤 Message vocal',
        'image' => '📷 Photo',
        'video' => '🎬 Vidéo',
        'file' => '📎 Fichier',
        'share' => '🔗 Élément partagé',
        'message' => 'Nouveau message',
    ],
    'errors' => [
        'self_conversation' => 'Vous ne pouvez pas démarrer une conversation avec vous-même.',
        'invalid_type' => 'Ce type de message n’est pas pris en charge.',
        'id_conflict' => 'Cet identifiant est déjà utilisé par un autre message.',
        'body_required' => 'Veuillez écrire un message.',
        'body_too_long' => 'Ce message est trop long.',
        'file_required' => 'Veuillez joindre un fichier.',
        'invalid_file' => 'Ce type de fichier n’est pas autorisé pour ce message.',
        'invalid_reply' => 'Le message auquel vous répondez ne fait pas partie de cette conversation.',
        'share_requires_kind' => 'Choisissez ce que vous partagez.',
        'not_owner' => 'Vous ne pouvez modifier que vos propres messages.',
        'not_editable' => 'Ce message ne peut plus être modifié.',
        'edit_window_expired' => 'Un message ne peut être modifié que dans les :minutes minutes suivant son envoi.',
        'conversation_not_found' => 'Conversation introuvable.',
        'message_not_found' => 'Message introuvable.',
        'attachment_not_found' => 'Pièce jointe introuvable.',
        'not_a_member' => 'Cette personne n’est pas membre de votre espace.',
        'clients_message_staff_only' => 'Vous pouvez uniquement écrire à votre équipe de coaching.',
        'clients_cannot_create_groups' => 'Seuls les coachs peuvent créer des conversations de groupe.',
        'broadcast_forbidden' => 'Vous n’avez pas l’autorisation d’envoyer des diffusions.',
        'broadcast_already_sent' => 'Cette diffusion a déjà été envoyée.',
    ],
];
