<?php

return [
    'default_title' => 'اتفاقية التدريب',
    'signature' => 'التوقيع',
    'audit_trail' => 'سجل التدقيق',
    'signed_by' => 'وقّعه',
    'signed_at' => 'وقت التوقيع (UTC)',
    'ip_address' => 'عنوان IP',
    'user_agent' => 'الجهاز',
    'document_hash' => 'بصمة المستند (SHA-256)',
    'awaiting_signature' => 'بانتظار التوقيع.',
    'errors' => [
        'not_signable' => 'تم توقيع هذا المستند مسبقًا أو لم يعد صالحًا.',
        'invalid_signature' => 'تعذّرت قراءة التوقيع. يرجى التوقيع مرة أخرى.',
    ],
];
