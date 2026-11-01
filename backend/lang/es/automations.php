<?php

return [
    'triggers' => [
        'client_joined' => 'Nuevo cliente',
        'program_started' => 'Programa iniciado',
        'program_finished' => 'Programa terminado',
        'workout_missed' => 'Entrenamientos perdidos',
        'checkin_overdue' => 'Revisión atrasada',
        'pr_hit' => 'Récord personal',
        'birthday' => 'Cumpleaños',
        'package_expiring' => 'Bono a punto de caducar',
        'payment_failed' => 'Pago fallido',
    ],
    'actions' => [
        'send_message' => 'Enviar un mensaje',
        'assign_habit' => 'Asignar un hábito',
        'assign_program' => 'Asignar un programa',
        'notify_coach' => 'Avisar al entrenador',
        'add_tag' => 'Añadir una etiqueta',
    ],
    'notify_coach' => [
        'title' => ':automation — :client',
        'body' => ':trigger: :client. :note',
    ],
    'errors' => [
        'invalid' => 'Revisa la configuración de la automatización.',
        'forbidden' => 'No tienes permiso para gestionar automatizaciones.',
        'template_not_found' => 'Esta plantilla de mensaje no existe.',
        'message_required' => 'Escribe el mensaje que quieres enviar o elige una plantilla.',
        'habit_not_found' => 'Elige un hábito de tu biblioteca.',
        'program_not_found' => 'Elige un programa de tu biblioteca.',
        'tag_required' => 'Escribe la etiqueta que quieres añadir.',
        'days_min' => 'El número de días debe ser al menos 1.',
    ],
];
