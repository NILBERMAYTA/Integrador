<?php

namespace App\Services;

use Illuminate\Support\Arr;

class OrdenPublicoDecisionService
{
    public function recomendar(array $contexto): array
    {
        $conducta = $contexto['conducta'] ?? 'cooperadora';
        $legalidad = $contexto['legalidad'] ?? 'por_verificar';
        $situacion = $contexto['situacion'] ?? 'manifestacion';
        $nivel = config("orden_publico.niveles.{$conducta}");

        abort_unless(is_array($nivel), 422, 'Nivel de conducta no reconocido.');

        $acciones = $nivel['acciones'];
        $salvaguardas = [
            'Aplicar legalidad, necesidad y proporcionalidad en toda decisión.',
            'Mantener trato digno, diferenciación y protección de quienes no participan en hechos violentos.',
        ];

        if ($legalidad === 'por_verificar') {
            array_unshift($acciones, 'Verificar el carácter lícito o ilícito según la conducta, las órdenes vigentes y la autoridad competente.');
        }

        if ($legalidad === 'licita' && $conducta === 'cooperadora') {
            $nivel['respuesta'] = 'Acompañamiento y protección de la reunión';
            $nivel['objetivo'] = 'Facilitar el ejercicio pacífico de derechos y proteger a participantes y terceros.';
        }

        if ($legalidad === 'ilicita' && $conducta === 'cooperadora') {
            $nivel['nivel'] = 'Negociación preventiva';
            $nivel['respuesta'] = 'Negociar dispersión y mantener acompañamiento';
            $nivel['riesgo'] = 'Moderado';
            $nivel['color'] = 'amber';
            array_unshift($acciones, 'Priorizar la negociación de una dispersión voluntaria antes de considerar otra intervención.');
        }

        if ($situacion === 'bloqueo') {
            $acciones[] = 'Coordinar tránsito, rutas alternativas y remoción segura de obstáculos bajo dirección del jefe táctico.';
        }

        if (($contexto['planificado'] ?? false) === true) {
            $acciones[] = 'Formalizar plan u orden de operaciones, línea de mando, comunicaciones, contingencias y apoyo interinstitucional.';
        } else {
            $acciones[] = 'Realizar apreciación rápida, establecer línea de mando y convertir la respuesta inicial en una planificación documentada.';
        }

        if (($contexto['vulnerables'] ?? false) === true) {
            $salvaguardas[] = 'Identificar y proteger a niñas, niños, personas mayores, personas con discapacidad y otros grupos vulnerables.';
            $salvaguardas[] = 'Prever rutas de salida y asistencia diferenciada antes de cualquier escalamiento.';
        }

        if (($contexto['zona_sensible'] ?? false) === true) {
            $salvaguardas[] = 'Extremar prevención por proximidad a hospitales, colegios, asilos o espacios cerrados.';
            $salvaguardas[] = 'Evitar acciones indiscriminadas con efectos difíciles de controlar en la zona sensible.';
        }

        if (in_array($conducta, ['resistencia_fisica', 'agresion_no_letal', 'agresion_letal'], true)) {
            $salvaguardas[] = 'Disponer asistencia médica, registro audiovisual cuando sea posible y parte detallado de la intervención.';
        }

        if ($conducta === 'agresion_letal') {
            $salvaguardas[] = 'La amenaza debe ser real e inminente y la respuesta debe ser selectiva, nunca indiscriminada contra la multitud.';
        }

        return [
            'nivel' => $nivel['nivel'],
            'respuesta' => $nivel['respuesta'],
            'riesgo' => $nivel['riesgo'],
            'color' => $nivel['color'],
            'objetivo' => $nivel['objetivo'],
            'acciones' => array_values(array_unique($acciones)),
            'salvaguardas' => array_values(array_unique($salvaguardas)),
            'referencias' => array_values(array_unique(array_merge(
                $nivel['referencias'],
                ['Págs. 59–64: legalidad, necesidad y proporcionalidad']
            ))),
            'resumen_contexto' => [
                config("orden_publico.situaciones.{$situacion}", $situacion),
                config("orden_publico.legalidades.{$legalidad}", $legalidad),
                config("orden_publico.conductas.{$conducta}", $conducta),
                config('orden_publico.magnitudes.'.Arr::get($contexto, 'magnitud'), 'Magnitud no indicada'),
            ],
        ];
    }
}
