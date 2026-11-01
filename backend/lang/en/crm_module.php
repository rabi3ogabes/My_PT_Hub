<?php

return [
    'activity' => [
        'stage_changed' => 'Moved from “:from” to “:to”',
        'converted' => 'Converted to client — invitation sent to :email',
    ],
    'errors' => [
        'unknown_stage' => 'This pipeline stage does not exist.',
        'unknown_owner' => 'The owner must be a member of your team.',
        'email_required' => 'Add an email address before converting this lead.',
        'not_a_client' => 'This person is not a client of your business.',
        'body_required' => 'Choose a template or write the document text.',
    ],
];
