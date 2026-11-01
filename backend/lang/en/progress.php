<?php

return [
    'errors' => [
        'invalid_signature' => 'This link has expired. Refresh to get a new one.',
        'unknown_measurement' => 'Unknown measurement.',
        'no_recipients' => 'Select at least one client.',
    ],
    'digest' => [
        'title' => ':count client(s) need your attention',
        'body' => 'At risk today: :names. Reach out before they drift.',
        'action' => 'Open compliance',
    ],
    'status' => [
        'green' => 'On track',
        'amber' => 'Needs attention',
        'red' => 'At risk',
    ],
    'reasons' => [
        'inactive' => 'Inactive for 3+ days',
        'score_drop' => 'Score dropped by more than 25%',
        'low_workouts' => 'Missing workouts',
        'low_nutrition' => 'Off nutrition targets',
        'low_habits' => 'Habits slipping',
        'low_checkins' => 'Missed check-ins',
    ],
];
