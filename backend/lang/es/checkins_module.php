<?php

return [
    'errors' => [
        'question_id' => 'Cada pregunta necesita un identificador (letras, números, - o _).',
        'question_id_duplicate' => 'Los identificadores de las preguntas deben ser únicos.',
        'question_type' => 'Tipo de pregunta desconocido.',
        'question_label' => 'Cada pregunta necesita un enunciado.',
        'question_options' => 'Las preguntas de opción necesitan entre 2 y 20 opciones.',
        'question_scale' => 'El mínimo debe ser menor que el máximo.',
        'questions_required' => 'Añade al menos una pregunta.',
        'invalid_answer' => 'Esta respuesta no es válida para la pregunta.',
        'photo_not_found' => 'No se encontraron una o más fotos.',
        'already_reviewed' => 'Este seguimiento ya ha sido revisado.',
        'not_submitted' => 'Este seguimiento aún no se ha enviado.',
    ],
    'notifications' => [
        'due_title' => 'Tu :form está listo',
        'due_body' => 'Dedica unos minutos a completar tu seguimiento del :date.',
        'reminder_title' => 'Recordatorio: :form',
        'reminder_body' => 'Tu seguimiento vence hoy. Tu entrenador lo está esperando.',
        'reviewed_title' => 'Tu entrenador ha revisado tu :form',
        'reviewed_body' => 'Abre la app para leer sus comentarios.',
        'overdue_title' => ':name no ha enviado un seguimiento',
        'overdue_body' => ':name no ha enviado «:form» (vencía el :date).',
        'submitted_title' => ':name ha enviado un seguimiento',
        'submitted_body' => ':name ha completado «:form». Revísalo ahora.',
        'comment_title' => 'Nuevo comentario de :name',
    ],
];
