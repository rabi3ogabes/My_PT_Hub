<?php

return [
    'triggers' => [
        'client_joined' => 'Client joined',
        'program_started' => 'Program started',
        'program_finished' => 'Program finished',
        'workout_missed' => 'Workouts missed',
        'checkin_overdue' => 'Check-in overdue',
        'pr_hit' => 'Personal record hit',
        'birthday' => 'Birthday',
        'package_expiring' => 'Package expiring',
        'payment_failed' => 'Payment failed',
    ],
    'actions' => [
        'send_message' => 'Send a message',
        'assign_habit' => 'Assign a habit',
        'assign_program' => 'Assign a program',
        'notify_coach' => 'Notify the coach',
        'add_tag' => 'Add a tag',
    ],
    'notify_coach' => [
        'title' => ':automation — :client',
        'body' => ':trigger: :client. :note',
    ],
    'errors' => [
        'invalid' => 'Please check the automation settings.',
        'forbidden' => 'You do not have permission to manage automations.',
        'template_not_found' => 'This message template does not exist.',
        'message_required' => 'Write the message to send or choose a template.',
        'habit_not_found' => 'Choose a habit from your library.',
        'program_not_found' => 'Choose a program from your library.',
        'tag_required' => 'Enter the tag to add.',
        'days_min' => 'The number of days must be at least 1.',
    ],
];
