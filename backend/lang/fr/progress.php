<?php

return [
    'errors' => [
        'invalid_signature' => 'Ce lien a expiré. Actualisez pour en obtenir un nouveau.',
        'unknown_measurement' => 'Mensuration inconnue.',
        'no_recipients' => 'Sélectionnez au moins un client.',
    ],
    'digest' => [
        'title' => ':count client(s) ont besoin de vous',
        'body' => 'À risque aujourd’hui : :names. Contactez-les avant qu’ils ne décrochent.',
        'action' => 'Ouvrir le suivi d’assiduité',
    ],
    'status' => [
        'green' => 'Sur la bonne voie',
        'amber' => 'À surveiller',
        'red' => 'À risque',
    ],
    'reasons' => [
        'inactive' => 'Inactif depuis 3 jours ou plus',
        'score_drop' => 'Score en baisse de plus de 25 %',
        'low_workouts' => 'Séances manquées',
        'low_nutrition' => 'Hors objectifs nutritionnels',
        'low_habits' => 'Habitudes en recul',
        'low_checkins' => 'Bilans manqués',
    ],
];
