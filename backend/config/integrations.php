<?php

use App\Domain\Integrations\Providers\Calendar\GoogleCalendarProvider;
use App\Domain\Integrations\Providers\Calendar\MicrosoftCalendarProvider;
use App\Domain\Integrations\Providers\Health\FitbitProvider;
use App\Domain\Integrations\Providers\Health\GarminProvider;
use App\Domain\Integrations\Providers\Health\OuraProvider;
use App\Domain\Integrations\Providers\Health\PolarProvider;
use App\Domain\Integrations\Providers\Health\StravaProvider;
use App\Domain\Integrations\Providers\Health\WhoopProvider;
use App\Domain\Integrations\Providers\Health\WithingsProvider;
use App\Domain\Integrations\Providers\Video\ZoomProvider;

/*
|--------------------------------------------------------------------------
| Integrations & marketplace configuration (Phase 7 / Phase 8 §4)
|--------------------------------------------------------------------------
| Provider credentials live in config/services.php; this file holds the
| catalogue, sync/normalization rules and webhook delivery settings.
*/

return [

    /*
    | Where the OAuth callback sends the user after connecting. The mobile apps
    | register the aurum:// scheme; web users land on the dashboard page.
    */
    'return_url' => env('INTEGRATIONS_RETURN_URL', '/app/integrations'),
    'allowed_return_prefixes' => array_filter(explode(',', (string) env('INTEGRATIONS_RETURN_PREFIXES', 'aurum://,/'))),

    'state_ttl_minutes' => 15,

    /*
    | Provider catalogue. category: health|calendar|video|messaging.
    | method: oauth (server OAuth flow), device (on-device SDK push), optin (consent only).
    | audience: who may connect it (client, staff).
    */
    'providers' => [
        'apple_health' => ['category' => 'health', 'method' => 'device', 'audience' => ['client']],
        'health_connect' => ['category' => 'health', 'method' => 'device', 'audience' => ['client']],
        'garmin' => ['category' => 'health', 'method' => 'oauth', 'audience' => ['client'], 'driver' => GarminProvider::class, 'webhook_token' => env('GARMIN_WEBHOOK_TOKEN')],
        'fitbit' => ['category' => 'health', 'method' => 'oauth', 'audience' => ['client'], 'driver' => FitbitProvider::class, 'subscriber_id' => env('FITBIT_SUBSCRIBER_ID', '1')],
        'strava' => ['category' => 'health', 'method' => 'oauth', 'audience' => ['client'], 'driver' => StravaProvider::class, 'subscription_id' => env('STRAVA_SUBSCRIPTION_ID')],
        'oura' => ['category' => 'health', 'method' => 'oauth', 'audience' => ['client'], 'driver' => OuraProvider::class, 'verification_token' => env('OURA_VERIFICATION_TOKEN')],
        'whoop' => ['category' => 'health', 'method' => 'oauth', 'audience' => ['client'], 'driver' => WhoopProvider::class],
        'withings' => ['category' => 'health', 'method' => 'oauth', 'audience' => ['client'], 'driver' => WithingsProvider::class, 'webhook_token' => env('WITHINGS_WEBHOOK_TOKEN')],
        'polar' => ['category' => 'health', 'method' => 'oauth', 'audience' => ['client'], 'driver' => PolarProvider::class, 'webhook_secret' => env('POLAR_WEBHOOK_SECRET')],
        'google_calendar' => ['category' => 'calendar', 'method' => 'oauth', 'audience' => ['staff'], 'driver' => GoogleCalendarProvider::class],
        'outlook' => ['category' => 'calendar', 'method' => 'oauth', 'audience' => ['staff'], 'driver' => MicrosoftCalendarProvider::class],
        'zoom' => ['category' => 'video', 'method' => 'oauth', 'audience' => ['staff'], 'driver' => ZoomProvider::class],
        'whatsapp' => ['category' => 'messaging', 'method' => 'optin', 'audience' => ['client', 'staff']],
    ],

    'health' => [
        // Accepted normalized sample types and their canonical units.
        'types' => [
            'steps' => 'count',
            'active_kcal' => 'kcal',
            'distance_m' => 'm',
            'heart_rate' => 'bpm',
            'resting_hr' => 'bpm',
            'hrv' => 'ms',
            'sleep_minutes' => 'min',
            'weight_kg' => 'kg',
            'body_fat_pct' => 'pct',
            'water_ml' => 'ml',
            'workout' => 'min',
            'readiness' => 'score',
            'blood_pressure_systolic' => 'mmHg',
            'blood_pressure_diastolic' => 'mmHg',
        ],

        // How samples of a type roll up into health_daily.
        'aggregation' => [
            'steps' => 'sum', 'active_kcal' => 'sum', 'distance_m' => 'sum', 'water_ml' => 'sum',
            'sleep_minutes' => 'sum', 'workout' => 'sum',
            'heart_rate' => 'avg', 'hrv' => 'avg', 'resting_hr' => 'min',
            'weight_kg' => 'last', 'body_fat_pct' => 'last', 'readiness' => 'last',
            'blood_pressure_systolic' => 'last', 'blood_pressure_diastolic' => 'last',
        ],

        /*
        | One source of truth per metric per day: the first source in the list
        | that has data wins. Per-type overrides fall back to "default".
        */
        'source_priority' => [
            'default' => ['garmin', 'oura', 'whoop', 'polar', 'fitbit', 'withings', 'apple_health', 'health_connect', 'strava', 'manual'],
            'weight_kg' => ['withings', 'garmin', 'fitbit', 'apple_health', 'health_connect', 'manual'],
            'body_fat_pct' => ['withings', 'garmin', 'fitbit', 'apple_health', 'health_connect', 'manual'],
            'sleep_minutes' => ['oura', 'whoop', 'garmin', 'fitbit', 'polar', 'apple_health', 'health_connect'],
            'hrv' => ['oura', 'whoop', 'garmin', 'polar', 'fitbit', 'apple_health', 'health_connect'],
            'water_ml' => ['apple_health', 'health_connect', 'fitbit', 'manual'],
        ],

        /*
        | Cross-source time-window dedupe for session-like samples: two samples
        | of the same type from different sources whose start times are within
        | `start_tolerance_minutes` and whose durations differ by at most
        | `duration_tolerance_pct` are the same real-world event.
        */
        'dedupe' => [
            'workout' => ['start_tolerance_minutes' => 10, 'duration_tolerance_pct' => 25],
            'sleep_minutes' => ['start_tolerance_minutes' => 45, 'duration_tolerance_pct' => 30],
            'weight_kg' => ['start_tolerance_minutes' => 15, 'duration_tolerance_pct' => 100],
            'body_fat_pct' => ['start_tolerance_minutes' => 15, 'duration_tolerance_pct' => 100],
        ],

        'max_batch' => 500,
        'baseline_days' => 28,
    ],

    'sync' => [
        'pull_interval_minutes' => 60,
        'backoff_base_minutes' => 5,
        'backoff_max_minutes' => 360,
        'max_failures' => 5,
        'backfill_days' => 30,
    ],

    'calendar' => [
        'busy_cache_seconds' => 300,
        'watch_ttl_hours' => 168,
    ],

    'webhooks' => [
        'events' => [
            'client.created', 'workout.completed', 'payment.succeeded', 'checkin.submitted',
            'lead.created', 'booking.created', 'booking.cancelled',
        ],
        'max_attempts' => 8,
        'timeout_seconds' => 10,
        'retention_days' => 30,
        'queue' => env('WEBHOOKS_QUEUE', 'default'),
    ],

    'api_tokens' => [
        'scopes' => [
            '*', 'clients:read', 'clients:write', 'programs:read', 'programs:write', 'messages:read', 'messages:write',
            'leads:read', 'leads:write', 'checkins:read', 'payments:read', 'calendar:read', 'calendar:write', 'webhooks:manage', 'health:read',
        ],
    ],

    'whatsapp' => [
        'graph_version' => env('WHATSAPP_GRAPH_VERSION', 'v21.0'),
        // App locale → WhatsApp template language code.
        'languages' => ['ar' => 'ar', 'fr' => 'fr', 'en' => 'en', 'es' => 'es'],
        // Approved template names and their ordered body parameters.
        'templates' => [
            'session_reminder' => ['name', 'session', 'time'],
            'payment_link' => ['name', 'amount', 'link'],
            'checkin_nudge' => ['name', 'coach'],
            'integration_reconnect' => ['name', 'provider'],
        ],
    ],

    'marketplace' => [
        'review_request_after_days' => 30,
        'review_request_window_days' => 30,
        'directory_per_page' => 20,
        'package_types' => ['coaching_package', 'membership', 'single_session', 'credit_pack', 'digital_program'],
    ],
];
