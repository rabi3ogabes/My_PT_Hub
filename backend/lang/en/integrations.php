<?php

return [
    'providers' => [
        'apple_health' => ['name' => 'Apple Health', 'description' => 'Steps, sleep, heart rate, workouts and body measurements from your iPhone and Apple Watch.'],
        'health_connect' => ['name' => 'Health Connect', 'description' => 'Steps, sleep, heart rate and workouts from Android apps and wearables.'],
        'garmin' => ['name' => 'Garmin', 'description' => 'Daily activity, workouts, sleep, HRV and body composition from Garmin Connect.'],
        'fitbit' => ['name' => 'Fitbit', 'description' => 'Steps, sleep, resting heart rate and weight from your Fitbit.'],
        'strava' => ['name' => 'Strava', 'description' => 'Runs, rides and other activities from Strava.'],
        'oura' => ['name' => 'Oura', 'description' => 'Readiness, sleep and activity from your Oura Ring.'],
        'whoop' => ['name' => 'WHOOP', 'description' => 'Recovery, strain, sleep and workouts from WHOOP.'],
        'withings' => ['name' => 'Withings', 'description' => 'Weight, body composition and activity from Withings devices.'],
        'polar' => ['name' => 'Polar', 'description' => 'Training sessions and daily activity from Polar Flow.'],
        'google_calendar' => ['name' => 'Google Calendar', 'description' => 'Two-way sync: bookings appear in Google Calendar and your busy times block booking slots.'],
        'outlook' => ['name' => 'Outlook Calendar', 'description' => 'Two-way sync with Outlook and Microsoft 365 calendars.'],
        'zoom' => ['name' => 'Zoom', 'description' => 'Automatic Zoom links for online sessions and live classes.'],
        'whatsapp' => ['name' => 'WhatsApp', 'description' => 'Reminders and updates on WhatsApp; replies arrive in your inbox.'],
    ],

    'errors' => [
        'unknown_provider' => 'This integration does not exist.',
        'not_oauth' => 'This integration is not connected through an account login.',
        'not_configured' => 'This integration is not available yet. Please try again later.',
        'not_for_role' => 'This integration is not available for your account.',
        'invalid_state' => 'The connection request expired or is invalid. Please start again.',
        'not_device' => 'This source is connected on the device, not here.',
        'invalid_phone' => 'Enter a valid mobile number with country code.',
        'not_connected' => 'This integration is not connected.',
        'sync_on_device' => 'This source syncs from your phone automatically.',
        'reconnect_required' => 'Please reconnect this integration.',
        'sync_throttled' => 'A sync was just requested. Please wait a minute.',
        'connection_broken' => 'The connection stopped working. Reconnect to resume syncing.',
        'client_not_found' => 'Client not found.',
        'invalid_range' => 'The date range is invalid.',
        'too_many_endpoints' => 'You have reached the maximum number of webhook endpoints.',
        'private_url' => 'The webhook URL must be a public HTTPS address.',
        'token_not_found' => 'API token not found.',
        'token_tenant' => 'This API token belongs to another business.',
        'token_scope' => 'This API token is missing the ":scope" scope.',
    ],

    'notifications' => [
        'reconnect_title' => 'Reconnect :provider',
        'reconnect_body' => 'We could not sync your :provider data. Reconnect it so your coach keeps seeing your progress.',
        'reconnect_action' => 'Reconnect',
    ],

    'readiness' => [
        'sleep' => [
            'label' => 'Sleep',
            'above' => 'Slept :pct% more than your usual.',
            'below' => 'Slept :pct% less than your usual.',
            'normal' => 'Sleep is in line with your usual.',
        ],
        'hrv' => [
            'label' => 'Heart rate variability',
            'above' => 'HRV is :pct% above your baseline — well recovered.',
            'below' => 'HRV is :pct% below your baseline — recovery may be incomplete.',
            'normal' => 'HRV is close to your baseline.',
        ],
        'resting_hr' => [
            'label' => 'Resting heart rate',
            'above' => 'Resting heart rate is :bpm bpm above your baseline.',
            'below' => 'Resting heart rate is :bpm bpm below your baseline.',
            'normal' => 'Resting heart rate is normal for you.',
        ],
        'training_load' => [
            'label' => 'Training load',
            'high' => 'Training load is ramping up fast (ratio :ratio). Consider an easier day.',
            'low' => 'Training load is lighter than usual (ratio :ratio).',
            'balanced' => 'Training load is balanced (ratio :ratio).',
        ],
    ],

    'whatsapp' => [
        'templates' => [
            'session_reminder' => 'Hi :name, a reminder that your :session is at :time. See you there!',
            'payment_link' => 'Hi :name, here is your payment link for :amount: :link',
            'checkin_nudge' => 'Hi :name, your weekly check-in is ready. :coach is looking forward to your update.',
            'integration_reconnect' => 'Hi :name, your :provider connection stopped syncing. Open the app to reconnect it.',
        ],
        'inbound_title' => 'WhatsApp message from :name',
        'media_received' => '[Media message received on WhatsApp]',
    ],
];
