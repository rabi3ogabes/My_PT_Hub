<?php

return [
    'triggers' => [
        'client_joined' => 'Nouveau client',
        'program_started' => 'Programme commencé',
        'program_finished' => 'Programme terminé',
        'workout_missed' => 'Séances manquées',
        'checkin_overdue' => 'Bilan en retard',
        'pr_hit' => 'Record personnel battu',
        'birthday' => 'Anniversaire',
        'package_expiring' => 'Forfait bientôt expiré',
        'payment_failed' => 'Échec de paiement',
    ],
    'actions' => [
        'send_message' => 'Envoyer un message',
        'assign_habit' => 'Attribuer une habitude',
        'assign_program' => 'Attribuer un programme',
        'notify_coach' => 'Prévenir le coach',
        'add_tag' => 'Ajouter une étiquette',
    ],
    'notify_coach' => [
        'title' => ':automation — :client',
        'body' => ':trigger : :client. :note',
    ],
    'errors' => [
        'invalid' => 'Veuillez vérifier les paramètres de l’automatisation.',
        'forbidden' => 'Vous n’avez pas l’autorisation de gérer les automatisations.',
        'template_not_found' => 'Ce modèle de message n’existe pas.',
        'message_required' => 'Rédigez le message à envoyer ou choisissez un modèle.',
        'habit_not_found' => 'Choisissez une habitude de votre bibliothèque.',
        'program_not_found' => 'Choisissez un programme de votre bibliothèque.',
        'tag_required' => 'Saisissez l’étiquette à ajouter.',
        'days_min' => 'Le nombre de jours doit être d’au moins 1.',
    ],
];
