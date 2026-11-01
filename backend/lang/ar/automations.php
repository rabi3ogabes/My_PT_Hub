<?php

return [
    'triggers' => [
        'client_joined' => 'انضمام عميل',
        'program_started' => 'بدء البرنامج',
        'program_finished' => 'انتهاء البرنامج',
        'workout_missed' => 'تفويت التمارين',
        'checkin_overdue' => 'تأخر المتابعة الدورية',
        'pr_hit' => 'تحقيق رقم قياسي شخصي',
        'birthday' => 'عيد الميلاد',
        'package_expiring' => 'قرب انتهاء الباقة',
        'payment_failed' => 'فشل الدفع',
    ],
    'actions' => [
        'send_message' => 'إرسال رسالة',
        'assign_habit' => 'تعيين عادة',
        'assign_program' => 'تعيين برنامج',
        'notify_coach' => 'تنبيه المدرب',
        'add_tag' => 'إضافة وسم',
    ],
    'notify_coach' => [
        'title' => ':automation — :client',
        'body' => ':trigger: :client. :note',
    ],
    'errors' => [
        'invalid' => 'يرجى مراجعة إعدادات الأتمتة.',
        'forbidden' => 'ليست لديك صلاحية إدارة الأتمتة.',
        'template_not_found' => 'قالب الرسالة هذا غير موجود.',
        'message_required' => 'اكتب الرسالة المراد إرسالها أو اختر قالبًا.',
        'habit_not_found' => 'اختر عادة من مكتبتك.',
        'program_not_found' => 'اختر برنامجًا من مكتبتك.',
        'tag_required' => 'أدخل الوسم المراد إضافته.',
        'days_min' => 'يجب ألا يقل عدد الأيام عن يوم واحد.',
    ],
];
