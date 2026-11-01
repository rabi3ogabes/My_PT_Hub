<?php

return [
    'default_title' => 'Acuerdo de entrenamiento',
    'signature' => 'Firma',
    'audit_trail' => 'Registro de auditoría',
    'signed_by' => 'Firmado por',
    'signed_at' => 'Fecha de firma (UTC)',
    'ip_address' => 'Dirección IP',
    'user_agent' => 'Dispositivo',
    'document_hash' => 'Huella del documento (SHA-256)',
    'awaiting_signature' => 'Pendiente de firma.',
    'errors' => [
        'not_signable' => 'Este documento ya se ha firmado o ya no es válido.',
        'invalid_signature' => 'No se pudo leer la firma. Vuelve a firmar, por favor.',
    ],
];
