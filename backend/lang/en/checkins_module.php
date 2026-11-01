<?php

return [
    'errors' => [
        'question_id' => 'Each question needs an id (letters, numbers, - or _).',
        'question_id_duplicate' => 'Question ids must be unique.',
        'question_type' => 'Unknown question type.',
        'question_label' => 'Each question needs a label.',
        'question_options' => 'Choice questions need 2 to 20 options.',
        'question_scale' => 'The minimum must be lower than the maximum.',
        'questions_required' => 'Add at least one question.',
        'invalid_answer' => 'This answer is not valid for the question.',
        'photo_not_found' => 'One or more photos could not be found.',
        'already_reviewed' => 'This check-in has already been reviewed.',
        'not_submitted' => 'This check-in has not been submitted yet.',
    ],
    'notifications' => [
        'due_title' => 'Your :form is ready',
        'due_body' => 'Take a few minutes to complete your check-in due on :date.',
        'reminder_title' => 'Reminder: :form',
        'reminder_body' => 'Your check-in is due today. Your coach is looking forward to it.',
        'reviewed_title' => 'Your coach reviewed your :form',
        'reviewed_body' => 'Open the app to read the feedback.',
        'overdue_title' => ':name missed a check-in',
        'overdue_body' => ':name has not submitted ":form" (due :date).',
        'submitted_title' => ':name submitted a check-in',
        'submitted_body' => ':name completed ":form". Review it now.',
        'comment_title' => 'New comment from :name',
    ],
];
