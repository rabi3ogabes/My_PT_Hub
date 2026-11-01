<?php

return [
    'preview' => [
        'voice' => '🎤 Nota de voz',
        'image' => '📷 Foto',
        'video' => '🎬 Vídeo',
        'file' => '📎 Archivo',
        'share' => '🔗 Elemento compartido',
        'message' => 'Mensaje nuevo',
    ],
    'errors' => [
        'self_conversation' => 'No puedes iniciar una conversación contigo mismo.',
        'invalid_type' => 'Este tipo de mensaje no es compatible.',
        'id_conflict' => 'Este identificador ya lo usa otro mensaje.',
        'body_required' => 'Escribe un mensaje.',
        'body_too_long' => 'El mensaje es demasiado largo.',
        'file_required' => 'Adjunta un archivo.',
        'invalid_file' => 'Este tipo de archivo no está permitido para este mensaje.',
        'invalid_reply' => 'El mensaje al que respondes no pertenece a esta conversación.',
        'share_requires_kind' => 'Elige qué quieres compartir.',
        'not_owner' => 'Solo puedes modificar tus propios mensajes.',
        'not_editable' => 'Este mensaje ya no se puede modificar.',
        'edit_window_expired' => 'Los mensajes solo se pueden editar durante los :minutes minutos posteriores al envío.',
        'conversation_not_found' => 'Conversación no encontrada.',
        'message_not_found' => 'Mensaje no encontrado.',
        'attachment_not_found' => 'Archivo adjunto no encontrado.',
        'not_a_member' => 'Esta persona no es miembro de tu espacio.',
        'clients_message_staff_only' => 'Solo puedes escribir a tu equipo de entrenamiento.',
        'clients_cannot_create_groups' => 'Solo los entrenadores pueden crear conversaciones de grupo.',
        'broadcast_forbidden' => 'No tienes permiso para enviar difusiones.',
        'broadcast_already_sent' => 'Esta difusión ya se ha enviado.',
    ],
];
