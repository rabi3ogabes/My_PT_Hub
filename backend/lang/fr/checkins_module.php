<?php

return [
    'errors' => [
        'question_id' => 'Chaque question doit avoir un identifiant (lettres, chiffres, - ou _).',
        'question_id_duplicate' => 'Les identifiants des questions doivent être uniques.',
        'question_type' => 'Type de question inconnu.',
        'question_label' => 'Chaque question doit avoir un libellé.',
        'question_options' => 'Les questions à choix nécessitent 2 à 20 options.',
        'question_scale' => 'Le minimum doit être inférieur au maximum.',
        'questions_required' => 'Ajoutez au moins une question.',
        'invalid_answer' => 'Cette réponse n’est pas valide pour la question.',
        'photo_not_found' => 'Une ou plusieurs photos sont introuvables.',
        'already_reviewed' => 'Ce bilan a déjà été examiné.',
        'not_submitted' => 'Ce bilan n’a pas encore été envoyé.',
    ],
    'notifications' => [
        'due_title' => 'Votre :form est prêt',
        'due_body' => 'Prenez quelques minutes pour remplir votre bilan prévu le :date.',
        'reminder_title' => 'Rappel : :form',
        'reminder_body' => 'Votre bilan est à rendre aujourd’hui. Votre coach l’attend avec impatience.',
        'reviewed_title' => 'Votre coach a examiné votre :form',
        'reviewed_body' => 'Ouvrez l’application pour lire ses retours.',
        'overdue_title' => ':name a manqué un bilan',
        'overdue_body' => ':name n’a pas envoyé « :form » (prévu le :date).',
        'submitted_title' => ':name a envoyé un bilan',
        'submitted_body' => ':name a rempli « :form ». Examinez-le maintenant.',
        'comment_title' => 'Nouveau commentaire de :name',
    ],
];
