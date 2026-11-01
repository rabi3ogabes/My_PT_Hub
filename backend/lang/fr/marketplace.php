<?php

return [
    'errors' => [
        'coach_not_found' => 'Ce profil de coach n’est pas disponible.',
        'invalid_rating' => 'La note doit être comprise entre 1 et 5.',
        'invalid_status' => 'Statut d’avis non valide.',
    ],

    'notifications' => [
        'new_lead_title' => 'Nouvelle demande de :name',
        'new_lead_body_profile' => ':name vous a contacté depuis votre profil public.',
        'new_lead_body_match' => ':name a été mis en relation avec vous par le service de recherche de coach et souhaite faire votre connaissance.',
        'new_lead_action' => 'Voir le prospect',
        'review_request_title' => 'Comment se passe votre coaching avec :coach ?',
        'review_request_body' => 'Vous vous entraînez avec :coach depuis un mois. Un court avis aide d’autres personnes à trouver le bon coach.',
        'review_request_action' => 'Laisser un avis',
    ],

    'lead_activity' => [
        'profile' => 'Demande reçue depuis le profil public du coach.',
        'match' => 'Mise en relation demandée via le questionnaire de recherche de coach.',
    ],

    'match' => [
        'default_message' => 'Bonjour ! Je cherche un coach pour m’aider dans mon objectif : :goal.',
        'reasons' => [
            'specialty' => 'Spécialiste de votre objectif',
            'language' => 'Parle votre langue',
            'online' => 'Coaching en ligne',
            'in_person' => 'Coaching en présentiel',
            'city' => 'Basé dans votre ville',
            'budget' => 'Dans votre budget',
            'rating' => 'Très bien noté par ses clients',
            'verified' => 'Diplômes vérifiés',
        ],
    ],

    'goals' => [
        'fat_loss' => 'perte de gras',
        'muscle_gain' => 'prise de muscle',
        'strength' => 'gain de force',
        'performance' => 'performance sportive',
        'health' => 'santé et bien-être',
    ],
];
