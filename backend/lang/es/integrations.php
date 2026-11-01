<?php

return [
    'providers' => [
        'apple_health' => ['name' => 'Apple Salud', 'description' => 'Pasos, sueño, frecuencia cardiaca, entrenamientos y medidas corporales desde tu iPhone y Apple Watch.'],
        'health_connect' => ['name' => 'Health Connect', 'description' => 'Pasos, sueño, frecuencia cardiaca y entrenamientos desde apps y wearables de Android.'],
        'garmin' => ['name' => 'Garmin', 'description' => 'Actividad diaria, entrenamientos, sueño, VFC y composición corporal desde Garmin Connect.'],
        'fitbit' => ['name' => 'Fitbit', 'description' => 'Pasos, sueño, frecuencia cardiaca en reposo y peso desde tu Fitbit.'],
        'strava' => ['name' => 'Strava', 'description' => 'Carreras, salidas en bici y otras actividades desde Strava.'],
        'oura' => ['name' => 'Oura', 'description' => 'Preparación, sueño y actividad desde tu anillo Oura.'],
        'whoop' => ['name' => 'WHOOP', 'description' => 'Recuperación, esfuerzo, sueño y entrenamientos desde WHOOP.'],
        'withings' => ['name' => 'Withings', 'description' => 'Peso, composición corporal y actividad desde tus dispositivos Withings.'],
        'polar' => ['name' => 'Polar', 'description' => 'Sesiones de entrenamiento y actividad diaria desde Polar Flow.'],
        'google_calendar' => ['name' => 'Google Calendar', 'description' => 'Sincronización bidireccional: las reservas aparecen en Google Calendar y tus horas ocupadas bloquean los huecos.'],
        'outlook' => ['name' => 'Calendario de Outlook', 'description' => 'Sincronización bidireccional con calendarios de Outlook y Microsoft 365.'],
        'zoom' => ['name' => 'Zoom', 'description' => 'Enlaces de Zoom automáticos para sesiones online y clases en directo.'],
        'whatsapp' => ['name' => 'WhatsApp', 'description' => 'Recordatorios y novedades por WhatsApp; las respuestas llegan a tu bandeja de entrada.'],
    ],

    'errors' => [
        'unknown_provider' => 'Esta integración no existe.',
        'not_oauth' => 'Esta integración no se conecta iniciando sesión en una cuenta.',
        'not_configured' => 'Esta integración aún no está disponible. Inténtalo más tarde.',
        'not_for_role' => 'Esta integración no está disponible para tu cuenta.',
        'invalid_state' => 'La solicitud de conexión ha caducado o no es válida. Vuelve a empezar.',
        'not_device' => 'Esta fuente se conecta desde el dispositivo, no desde aquí.',
        'invalid_phone' => 'Introduce un número de móvil válido con el prefijo del país.',
        'not_connected' => 'Esta integración no está conectada.',
        'sync_on_device' => 'Esta fuente se sincroniza automáticamente desde tu teléfono.',
        'reconnect_required' => 'Vuelve a conectar esta integración.',
        'sync_throttled' => 'Acabas de solicitar una sincronización. Espera un minuto.',
        'connection_broken' => 'La conexión ha dejado de funcionar. Vuelve a conectarla para reanudar la sincronización.',
        'client_not_found' => 'Cliente no encontrado.',
        'invalid_range' => 'El rango de fechas no es válido.',
        'too_many_endpoints' => 'Has alcanzado el número máximo de endpoints de webhook.',
        'private_url' => 'La URL del webhook debe ser una dirección HTTPS pública.',
        'token_not_found' => 'Token de API no encontrado.',
        'token_tenant' => 'Este token de API pertenece a otro negocio.',
        'token_scope' => 'Este token de API no tiene el permiso «:scope».',
    ],

    'notifications' => [
        'reconnect_title' => 'Vuelve a conectar :provider',
        'reconnect_body' => 'No hemos podido sincronizar tus datos de :provider. Vuelve a conectarlo para que tu entrenador siga viendo tu progreso.',
        'reconnect_action' => 'Volver a conectar',
    ],

    'readiness' => [
        'sleep' => [
            'label' => 'Sueño',
            'above' => 'Has dormido un :pct % más de lo habitual.',
            'below' => 'Has dormido un :pct % menos de lo habitual.',
            'normal' => 'Tu sueño está en línea con lo habitual.',
        ],
        'hrv' => [
            'label' => 'Variabilidad de la frecuencia cardiaca',
            'above' => 'Tu VFC está un :pct % por encima de tu referencia: buena recuperación.',
            'below' => 'Tu VFC está un :pct % por debajo de tu referencia: la recuperación puede ser incompleta.',
            'normal' => 'Tu VFC está cerca de tu referencia.',
        ],
        'resting_hr' => [
            'label' => 'Frecuencia cardiaca en reposo',
            'above' => 'Tu frecuencia cardiaca en reposo está :bpm lpm por encima de tu referencia.',
            'below' => 'Tu frecuencia cardiaca en reposo está :bpm lpm por debajo de tu referencia.',
            'normal' => 'Tu frecuencia cardiaca en reposo es normal para ti.',
        ],
        'training_load' => [
            'label' => 'Carga de entrenamiento',
            'high' => 'Tu carga de entrenamiento sube rápido (ratio :ratio). Plantéate un día más suave.',
            'low' => 'Tu carga de entrenamiento es más ligera de lo habitual (ratio :ratio).',
            'balanced' => 'Tu carga de entrenamiento está equilibrada (ratio :ratio).',
        ],
    ],

    'whatsapp' => [
        'templates' => [
            'session_reminder' => 'Hola :name, te recordamos que tu :session es a las :time. ¡Nos vemos!',
            'payment_link' => 'Hola :name, aquí tienes tu enlace de pago de :amount: :link',
            'checkin_nudge' => 'Hola :name, tu revisión semanal está lista. :coach espera tu actualización.',
            'integration_reconnect' => 'Hola :name, la sincronización de :provider se ha detenido. Abre la app para volver a conectarla.',
        ],
        'inbound_title' => 'Mensaje de WhatsApp de :name',
        'media_received' => '[Mensaje multimedia recibido por WhatsApp]',
    ],
];
