<?php

return [
    'errors' => [
        'coach_not_found' => 'الملف التعريفي لهذا المدرب غير متاح.',
        'invalid_rating' => 'يجب أن يكون التقييم بين 1 و5.',
        'invalid_status' => 'حالة المراجعة غير صالحة.',
    ],

    'notifications' => [
        'new_lead_title' => 'استفسار جديد من :name',
        'new_lead_body_profile' => 'تواصل معك :name عبر ملفك التعريفي العام.',
        'new_lead_body_match' => 'رشّحك نظام البحث عن مدرب لـ :name ويرغب في التعرّف عليك.',
        'new_lead_action' => 'عرض العميل المحتمل',
        'review_request_title' => 'كيف تسير تجربتك التدريبية مع :coach؟',
        'review_request_body' => 'مضى شهر على تدريبك مع :coach. يساعد تقييمك القصير الآخرين على اختيار المدرب المناسب.',
        'review_request_action' => 'اكتب تقييمًا',
    ],

    'lead_activity' => [
        'profile' => 'تم استلام استفسار عبر الملف التعريفي العام للمدرب.',
        'match' => 'طُلب التعارف عبر استبيان مطابقة المدربين.',
    ],

    'match' => [
        'default_message' => 'مرحبًا! أبحث عن مدرب يساعدني في :goal.',
        'reasons' => [
            'specialty' => 'متخصص في هدفك',
            'language' => 'يتحدث لغتك',
            'online' => 'يدرّب عبر الإنترنت',
            'in_person' => 'يدرّب حضوريًا',
            'city' => 'مقيم في مدينتك',
            'budget' => 'ضمن ميزانيتك',
            'rating' => 'تقييمات عالية من العملاء',
            'verified' => 'شهادات موثّقة',
        ],
    ],

    'goals' => [
        'fat_loss' => 'خسارة الدهون',
        'muscle_gain' => 'بناء العضلات',
        'strength' => 'زيادة القوة',
        'performance' => 'تحسين الأداء الرياضي',
        'health' => 'الصحة والعافية',
    ],
];
