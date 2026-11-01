<?php

return [
    'providers' => [
        'apple_health' => ['name' => 'Apple Santé', 'description' => 'Pas, sommeil, fréquence cardiaque, séances et mensurations depuis votre iPhone et votre Apple Watch.'],
        'health_connect' => ['name' => 'Health Connect', 'description' => 'Pas, sommeil, fréquence cardiaque et séances depuis vos applications et objets connectés Android.'],
        'garmin' => ['name' => 'Garmin', 'description' => 'Activité quotidienne, séances, sommeil, VFC et composition corporelle depuis Garmin Connect.'],
        'fitbit' => ['name' => 'Fitbit', 'description' => 'Pas, sommeil, fréquence cardiaque au repos et poids depuis votre Fitbit.'],
        'strava' => ['name' => 'Strava', 'description' => 'Courses, sorties vélo et autres activités depuis Strava.'],
        'oura' => ['name' => 'Oura', 'description' => 'Forme, sommeil et activité depuis votre bague Oura.'],
        'whoop' => ['name' => 'WHOOP', 'description' => 'Récupération, effort, sommeil et séances depuis WHOOP.'],
        'withings' => ['name' => 'Withings', 'description' => 'Poids, composition corporelle et activité depuis vos appareils Withings.'],
        'polar' => ['name' => 'Polar', 'description' => 'Séances d’entraînement et activité quotidienne depuis Polar Flow.'],
        'google_calendar' => ['name' => 'Google Agenda', 'description' => 'Synchronisation bidirectionnelle : les réservations apparaissent dans Google Agenda et vos indisponibilités bloquent les créneaux.'],
        'outlook' => ['name' => 'Calendrier Outlook', 'description' => 'Synchronisation bidirectionnelle avec les calendriers Outlook et Microsoft 365.'],
        'zoom' => ['name' => 'Zoom', 'description' => 'Liens Zoom automatiques pour les séances en ligne et les cours en direct.'],
        'whatsapp' => ['name' => 'WhatsApp', 'description' => 'Rappels et mises à jour sur WhatsApp ; les réponses arrivent dans votre messagerie.'],
    ],

    'errors' => [
        'unknown_provider' => 'Cette intégration n’existe pas.',
        'not_oauth' => 'Cette intégration ne se connecte pas via une connexion à un compte.',
        'not_configured' => 'Cette intégration n’est pas encore disponible. Veuillez réessayer plus tard.',
        'not_for_role' => 'Cette intégration n’est pas disponible pour votre compte.',
        'invalid_state' => 'La demande de connexion a expiré ou n’est pas valide. Veuillez recommencer.',
        'not_device' => 'Cette source se connecte depuis l’appareil, pas ici.',
        'invalid_phone' => 'Saisissez un numéro de mobile valide avec l’indicatif du pays.',
        'not_connected' => 'Cette intégration n’est pas connectée.',
        'sync_on_device' => 'Cette source se synchronise automatiquement depuis votre téléphone.',
        'reconnect_required' => 'Veuillez reconnecter cette intégration.',
        'sync_throttled' => 'Une synchronisation vient d’être demandée. Veuillez patienter une minute.',
        'connection_broken' => 'La connexion ne fonctionne plus. Reconnectez-la pour reprendre la synchronisation.',
        'client_not_found' => 'Client introuvable.',
        'invalid_range' => 'La période indiquée n’est pas valide.',
        'too_many_endpoints' => 'Vous avez atteint le nombre maximal de points de terminaison webhook.',
        'private_url' => 'L’URL du webhook doit être une adresse HTTPS publique.',
        'token_not_found' => 'Jeton d’API introuvable.',
        'token_tenant' => 'Ce jeton d’API appartient à une autre entreprise.',
        'token_scope' => 'Ce jeton d’API ne dispose pas de la portée « :scope ».',
    ],

    'notifications' => [
        'reconnect_title' => 'Reconnectez :provider',
        'reconnect_body' => 'Nous n’avons pas pu synchroniser vos données :provider. Reconnectez-le pour que votre coach continue de suivre vos progrès.',
        'reconnect_action' => 'Reconnecter',
    ],

    'readiness' => [
        'sleep' => [
            'label' => 'Sommeil',
            'above' => 'Vous avez dormi :pct % de plus que d’habitude.',
            'below' => 'Vous avez dormi :pct % de moins que d’habitude.',
            'normal' => 'Votre sommeil est conforme à vos habitudes.',
        ],
        'hrv' => [
            'label' => 'Variabilité de la fréquence cardiaque',
            'above' => 'Votre VFC est :pct % au-dessus de votre référence : bonne récupération.',
            'below' => 'Votre VFC est :pct % en dessous de votre référence : récupération peut-être incomplète.',
            'normal' => 'Votre VFC est proche de votre référence.',
        ],
        'resting_hr' => [
            'label' => 'Fréquence cardiaque au repos',
            'above' => 'Votre fréquence cardiaque au repos est :bpm bpm au-dessus de votre référence.',
            'below' => 'Votre fréquence cardiaque au repos est :bpm bpm en dessous de votre référence.',
            'normal' => 'Votre fréquence cardiaque au repos est normale pour vous.',
        ],
        'training_load' => [
            'label' => 'Charge d’entraînement',
            'high' => 'Votre charge d’entraînement augmente rapidement (ratio :ratio). Envisagez une journée plus légère.',
            'low' => 'Votre charge d’entraînement est plus légère que d’habitude (ratio :ratio).',
            'balanced' => 'Votre charge d’entraînement est équilibrée (ratio :ratio).',
        ],
    ],

    'whatsapp' => [
        'templates' => [
            'session_reminder' => 'Bonjour :name, petit rappel : votre :session a lieu à :time. À tout à l’heure !',
            'payment_link' => 'Bonjour :name, voici votre lien de paiement pour :amount : :link',
            'checkin_nudge' => 'Bonjour :name, votre bilan hebdomadaire est prêt. :coach attend votre retour avec impatience.',
            'integration_reconnect' => 'Bonjour :name, la synchronisation de :provider s’est arrêtée. Ouvrez l’application pour la reconnecter.',
        ],
        'inbound_title' => 'Message WhatsApp de :name',
        'media_received' => '[Message multimédia reçu sur WhatsApp]',
    ],
];
