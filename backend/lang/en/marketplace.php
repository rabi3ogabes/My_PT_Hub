<?php

return [
    'errors' => [
        'coach_not_found' => 'This coach profile is not available.',
        'invalid_rating' => 'The rating must be between 1 and 5.',
        'invalid_status' => 'Invalid review status.',
    ],

    'notifications' => [
        'new_lead_title' => 'New enquiry from :name',
        'new_lead_body_profile' => ':name contacted you from your public profile.',
        'new_lead_body_match' => ':name was matched with you by the coach finder and would like an introduction.',
        'new_lead_action' => 'View lead',
        'review_request_title' => 'How is your coaching with :coach going?',
        'review_request_body' => 'You have been training with :coach for a month. A short review helps other people find the right coach.',
        'review_request_action' => 'Leave a review',
    ],

    'lead_activity' => [
        'profile' => 'Enquiry received from the public coach profile.',
        'match' => 'Introduction requested through the coach-matching quiz.',
    ],

    'match' => [
        'default_message' => 'Hi! I am looking for a coach to help me with :goal.',
        'reasons' => [
            'specialty' => 'Specialises in your goal',
            'language' => 'Speaks your language',
            'online' => 'Coaches online',
            'in_person' => 'Trains in person',
            'city' => 'Based in your city',
            'budget' => 'Within your budget',
            'rating' => 'Highly rated by clients',
            'verified' => 'Verified credentials',
        ],
    ],

    'goals' => [
        'fat_loss' => 'fat loss',
        'muscle_gain' => 'building muscle',
        'strength' => 'getting stronger',
        'performance' => 'sports performance',
        'health' => 'health and wellbeing',
    ],
];
