<?php

return [
    'errors' => [
        'invalid_signature' => 'Este enlace ha caducado. Actualiza para obtener uno nuevo.',
        'unknown_measurement' => 'Medida desconocida.',
        'no_recipients' => 'Selecciona al menos un cliente.',
    ],
    'digest' => [
        'title' => ':count cliente(s) necesitan tu atención',
        'body' => 'En riesgo hoy: :names. Contacta con ellos antes de que se desconecten.',
        'action' => 'Abrir cumplimiento',
    ],
    'status' => [
        'green' => 'En buen camino',
        'amber' => 'Requiere atención',
        'red' => 'En riesgo',
    ],
    'reasons' => [
        'inactive' => 'Inactivo 3 días o más',
        'score_drop' => 'La puntuación bajó más de un 25 %',
        'low_workouts' => 'Entrenamientos perdidos',
        'low_nutrition' => 'Fuera de los objetivos nutricionales',
        'low_habits' => 'Hábitos en descenso',
        'low_checkins' => 'Seguimientos no enviados',
    ],
];
