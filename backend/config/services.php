<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Third Party Services
    |--------------------------------------------------------------------------
    |
    | This file is for storing the credentials for third party services such
    | as Resend, Postmark, AWS, and more. This file provides the de facto
    | location for this type of information, allowing packages to have
    | a conventional file to locate the various service credentials.
    |
    */

    'postmark' => [
        'key' => env('POSTMARK_API_KEY'),
    ],

    'resend' => [
        'key' => env('RESEND_API_KEY'),
    ],

    'ses' => [
        'key' => env('AWS_ACCESS_KEY_ID'),
        'secret' => env('AWS_SECRET_ACCESS_KEY'),
        'region' => env('AWS_DEFAULT_REGION', 'us-east-1'),
    ],

    'slack' => [
        'notifications' => [
            'bot_user_oauth_token' => env('SLACK_BOT_USER_OAUTH_TOKEN'),
            'channel' => env('SLACK_BOT_USER_DEFAULT_CHANNEL'),
        ],
    ],

    'fcm' => [
        'credentials' => env('FIREBASE_CREDENTIALS'),
    ],

    'stripe' => [
        'secret' => env('STRIPE_SECRET'),
        'key' => env('STRIPE_KEY'),
        'webhook_secret' => env('STRIPE_WEBHOOK_SECRET'),
        'connect_client_id' => env('STRIPE_CONNECT_CLIENT_ID'),
    ],

    'ai' => [
        'driver' => env('AI_DRIVER', 'fake'), // anthropic|openai|fake
        'anthropic_key' => env('ANTHROPIC_API_KEY'),
        'anthropic_model' => env('ANTHROPIC_MODEL', 'claude-sonnet-5-5'),
        'anthropic_vision_model' => env('ANTHROPIC_VISION_MODEL', 'claude-sonnet-5-5'),
        'openai_key' => env('OPENAI_API_KEY'),
        'openai_model' => env('OPENAI_MODEL', 'gpt-4.1-mini'),
        'monthly_cap_micros' => (int) env('AI_TENANT_MONTHLY_CAP_MICROS', 50_000_000),
    ],

    'google' => [
        'client_id' => env('GOOGLE_CLIENT_ID'),
        'client_secret' => env('GOOGLE_CLIENT_SECRET'),
        'ios_client_id' => env('GOOGLE_IOS_CLIENT_ID'),
        'android_client_id' => env('GOOGLE_ANDROID_CLIENT_ID'),
        'redirect' => env('GOOGLE_REDIRECT_URI', '/auth/google/callback'),
    ],

    'apple' => [
        'client_id' => env('APPLE_CLIENT_ID'), // services id (web) or bundle id
        'bundle_ids' => array_filter(explode(',', (string) env('APPLE_BUNDLE_IDS', 'coach.aurum.client,coach.aurum.coach'))),
    ],

    'whatsapp' => [
        'token' => env('WHATSAPP_TOKEN'),
        'phone_number_id' => env('WHATSAPP_PHONE_NUMBER_ID'),
        'verify_token' => env('WHATSAPP_VERIFY_TOKEN'),
        'app_secret' => env('WHATSAPP_APP_SECRET'),
    ],

    'open_food_facts' => [
        'base_url' => env('OFF_BASE_URL', 'https://world.openfoodfacts.org'),
    ],

    'garmin' => ['key' => env('GARMIN_CONSUMER_KEY'), 'secret' => env('GARMIN_CONSUMER_SECRET')],
    'fitbit' => ['client_id' => env('FITBIT_CLIENT_ID'), 'client_secret' => env('FITBIT_CLIENT_SECRET'), 'verify' => env('FITBIT_SUBSCRIBER_VERIFY')],
    'strava' => ['client_id' => env('STRAVA_CLIENT_ID'), 'client_secret' => env('STRAVA_CLIENT_SECRET'), 'verify_token' => env('STRAVA_VERIFY_TOKEN')],
    'oura' => ['client_id' => env('OURA_CLIENT_ID'), 'client_secret' => env('OURA_CLIENT_SECRET')],
    'whoop' => ['client_id' => env('WHOOP_CLIENT_ID'), 'client_secret' => env('WHOOP_CLIENT_SECRET')],
    'withings' => ['client_id' => env('WITHINGS_CLIENT_ID'), 'client_secret' => env('WITHINGS_CLIENT_SECRET')],
    'polar' => ['client_id' => env('POLAR_CLIENT_ID'), 'client_secret' => env('POLAR_CLIENT_SECRET')],
    'google_calendar' => ['client_id' => env('GOOGLE_CLIENT_ID'), 'client_secret' => env('GOOGLE_CLIENT_SECRET')],
    'microsoft' => ['client_id' => env('MICROSOFT_CLIENT_ID'), 'client_secret' => env('MICROSOFT_CLIENT_SECRET')],
    'zoom' => ['client_id' => env('ZOOM_CLIENT_ID'), 'client_secret' => env('ZOOM_CLIENT_SECRET')],

];
