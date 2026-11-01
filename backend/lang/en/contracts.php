<?php

return [
    'default_title' => 'Coaching agreement',
    'signature' => 'Signature',
    'audit_trail' => 'Audit trail',
    'signed_by' => 'Signed by',
    'signed_at' => 'Signed at (UTC)',
    'ip_address' => 'IP address',
    'user_agent' => 'Device',
    'document_hash' => 'Document fingerprint (SHA-256)',
    'awaiting_signature' => 'Awaiting signature.',
    'errors' => [
        'not_signable' => 'This document has already been signed or is no longer valid.',
        'invalid_signature' => 'The signature could not be read. Please sign again.',
    ],
];
