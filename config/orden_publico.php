<?php

return [
    'situaciones' => [
        'manifestacion' => 'Manifestación o marcha',
        'bloqueo' => 'Bloqueo de vía',
        'concentracion' => 'Concentración o reunión',
        'disturbio' => 'Disturbio en desarrollo',
    ],

    'legalidades' => [
        'por_verificar' => 'Por verificar',
        'licita' => 'Lícita o autorizada',
        'ilicita' => 'Ilícita o con orden de dispersión',
    ],

    'conductas' => [
        'cooperadora' => 'Cooperadora y pacífica',
        'no_cooperadora' => 'No cooperadora, sin agresión física',
        'resistencia_fisica' => 'Resistencia física',
        'agresion_no_letal' => 'Agresión con riesgo de lesiones',
        'agresion_letal' => 'Amenaza real e inminente de muerte o lesión grave',
    ],

    'magnitudes' => [
        'reducida' => 'Hasta 50 personas',
        'media' => 'De 51 a 200 personas',
        'alta' => 'De 201 a 500 personas',
        'masiva' => 'Más de 500 personas',
    ],

    'niveles' => [
        'cooperadora' => [
            'nivel' => 'Preventivo',
            'respuesta' => 'Acompañamiento y comunicación',
            'riesgo' => 'Bajo',
            'color' => 'emerald',
            'objetivo' => 'Garantizar derechos, seguridad, circulación y prevención de incidentes.',
            'acciones' => [
                'Mantener comunicación con organizadores o líderes identificados.',
                'Coordinar recorrido, horarios, tránsito, rutas alternativas y asistencia médica.',
                'Aplicar presencia profesional, firme y no provocadora.',
                'Observar cambios de conducta y actualizar la apreciación de situación.',
            ],
            'referencias' => ['Págs. 39–42', 'Págs. 46–47', 'Págs. 65–67'],
        ],
        'no_cooperadora' => [
            'nivel' => 'Diálogo reforzado',
            'respuesta' => 'Disuasión, persuasión, mediación y negociación',
            'riesgo' => 'Moderado',
            'color' => 'amber',
            'objetivo' => 'Lograr cooperación voluntaria y evitar que la situación escale.',
            'acciones' => [
                'Designar comunicador o negociador distinto del jefe operativo.',
                'Emitir instrucciones claras, comprensibles y dar tiempo razonable para cumplirlas.',
                'Mantener contacto visual, contención preventiva y vías seguras de salida.',
                'Registrar acuerdos, advertencias y cambios relevantes de conducta.',
            ],
            'referencias' => ['Págs. 41–42', 'Págs. 47–48', 'Págs. 66–71'],
        ],
        'resistencia_fisica' => [
            'nivel' => 'Intervención controlada',
            'respuesta' => 'Control selectivo por personal capacitado',
            'riesgo' => 'Alto',
            'color' => 'orange',
            'objetivo' => 'Controlar la resistencia con el mínimo nivel necesario y evitar lesiones.',
            'acciones' => [
                'Confirmar objetivo legal, necesidad inmediata y proporcionalidad.',
                'Priorizar intervención selectiva sobre quienes ofrecen resistencia.',
                'Mantener advertencias y comunicación durante la intervención.',
                'Cesar la fuerza inmediatamente cuando cese la resistencia.',
            ],
            'referencias' => ['Págs. 61–68', 'Págs. 112–113'],
        ],
        'agresion_no_letal' => [
            'nivel' => 'Respuesta especializada',
            'respuesta' => 'Protección y neutralización diferenciada',
            'riesgo' => 'Muy alto',
            'color' => 'rose',
            'objetivo' => 'Detener agresiones y proteger a las personas con una respuesta diferenciada.',
            'acciones' => [
                'Activar línea de mando y personal especializado conforme al plan u orden de operaciones.',
                'Aislar a quienes agreden de participantes no violentos y terceros.',
                'Mantener advertencias, vías de salida y corredores para asistencia.',
                'Preparar asistencia médica y documentación completa de la intervención.',
            ],
            'referencias' => ['Págs. 61–72', 'Págs. 102–104', 'Págs. 111–113'],
        ],
        'agresion_letal' => [
            'nivel' => 'Protección urgente de la vida',
            'respuesta' => 'Respuesta individualizada ante amenaza letal',
            'riesgo' => 'Crítico',
            'color' => 'red',
            'objetivo' => 'Proteger la vida frente a una amenaza real, manifiesta e inminente.',
            'acciones' => [
                'Activar inmediatamente mando, emergencia médica y equipo especializado autorizado.',
                'Diferenciar e individualizar a la persona que genera la amenaza; nunca tratar a toda la multitud como agresora.',
                'Usar únicamente la respuesta necesaria para detener la amenaza y cesarla cuando termine.',
                'Preservar evidencia, identificar testigos y emitir el informe correspondiente.',
            ],
            'referencias' => ['Págs. 61–64', 'Págs. 73–74', 'Págs. 112–113'],
        ],
    ],
];
