<?php

return [
    'default_title' => 'Contrat de coaching',
    'signature' => 'Signature',
    'audit_trail' => 'Piste d’audit',
    'signed_by' => 'Signé par',
    'signed_at' => 'Signé le (UTC)',
    'ip_address' => 'Adresse IP',
    'user_agent' => 'Appareil',
    'document_hash' => 'Empreinte du document (SHA-256)',
    'awaiting_signature' => 'En attente de signature.',
    'errors' => [
        'not_signable' => 'Ce document a déjà été signé ou n’est plus valide.',
        'invalid_signature' => 'La signature est illisible. Veuillez signer à nouveau.',
    ],
];
