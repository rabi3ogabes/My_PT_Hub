<?php

return [
    'name' => env('BRAND_NAME', 'Aurum'),
    'tagline' => env('BRAND_TAGLINE', 'The coaching platform for professionals'),
    'accent' => env('BRAND_ACCENT', '#D4AF6A'),
    'support_email' => env('BRAND_SUPPORT_EMAIL', 'support@aurum.coach'),
    'domain' => env('BRAND_DOMAIN', 'aurum.coach'),
    'ios_app_id' => env('IOS_APP_ID'),
    'ios_coach_app_id' => env('IOS_COACH_APP_ID'),
    'android_package' => env('ANDROID_PACKAGE', 'coach.aurum.client'),
    'android_coach_package' => env('ANDROID_COACH_PACKAGE', 'coach.aurum.coach'),
    'apple_team_id' => env('APPLE_TEAM_ID'),
    'android_sha256' => array_filter(explode(',', (string) env('ANDROID_SHA256_FINGERPRINTS', ''))),
    'locales' => ['en', 'ar', 'fr', 'es'],
];
