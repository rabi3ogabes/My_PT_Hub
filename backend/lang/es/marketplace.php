<?php

return [
    'errors' => [
        'coach_not_found' => 'Este perfil de entrenador no está disponible.',
        'invalid_rating' => 'La valoración debe estar entre 1 y 5.',
        'invalid_status' => 'Estado de reseña no válido.',
    ],

    'notifications' => [
        'new_lead_title' => 'Nueva consulta de :name',
        'new_lead_body_profile' => ':name te ha contactado desde tu perfil público.',
        'new_lead_body_match' => 'El buscador de entrenadores ha emparejado a :name contigo y quiere conocerte.',
        'new_lead_action' => 'Ver contacto',
        'review_request_title' => '¿Qué tal va tu entrenamiento con :coach?',
        'review_request_body' => 'Llevas un mes entrenando con :coach. Una breve reseña ayuda a otras personas a encontrar al entrenador adecuado.',
        'review_request_action' => 'Escribir una reseña',
    ],

    'lead_activity' => [
        'profile' => 'Consulta recibida desde el perfil público del entrenador.',
        'match' => 'Presentación solicitada a través del cuestionario de búsqueda de entrenador.',
    ],

    'match' => [
        'default_message' => '¡Hola! Busco un entrenador que me ayude con :goal.',
        'reasons' => [
            'specialty' => 'Especialista en tu objetivo',
            'language' => 'Habla tu idioma',
            'online' => 'Entrena online',
            'in_person' => 'Entrena en persona',
            'city' => 'Está en tu ciudad',
            'budget' => 'Dentro de tu presupuesto',
            'rating' => 'Muy bien valorado por sus clientes',
            'verified' => 'Titulaciones verificadas',
        ],
    ],

    'goals' => [
        'fat_loss' => 'perder grasa',
        'muscle_gain' => 'ganar músculo',
        'strength' => 'ganar fuerza',
        'performance' => 'el rendimiento deportivo',
        'health' => 'la salud y el bienestar',
    ],
];
