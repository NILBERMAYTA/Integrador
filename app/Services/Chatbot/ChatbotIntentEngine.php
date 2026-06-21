<?php

namespace App\Services\Chatbot;

use Carbon\CarbonImmutable;
use Illuminate\Support\Str;

class ChatbotIntentEngine
{
    public function detect(string $message): ChatbotIntentResult
    {
        $normalized = $this->canonicalize($this->normalize($message));
        [$from, $to] = $this->period($normalized);
        $status = $this->status($normalized);
        $operation = $this->operation($normalized);

        $intent = match (true) {
            $this->containsAny($normalized, ['ayuda', 'que puedes hacer', 'que sabes hacer', 'alcance', 'capacidades']) =>
                ChatbotIntent::Help,

            $this->containsAny($normalized, ['proxim', 'por vencer', 'mantenimiento pronto', 'preventivo pendiente', 'requiere mantenimiento']) =>
                ChatbotIntent::MaintenanceDue,

            $operation === 'devolucion' && $this->containsAny($normalized, ['incidenc', 'novedad', 'dano', 'defecto']) =>
                ChatbotIntent::ReturnsWithIncidents,

            $operation === 'devolucion' && $this->containsAny($normalized, ['pendient', 'falta', 'sin devolver', 'no devuelt']) =>
                ChatbotIntent::ActiveLoans,

            $this->containsAny($normalized, ['mas prestad', 'mas solicitad', 'mayor prestamo', 'mas usad', 'mas entregad', 'mayor movimiento']) =>
                ChatbotIntent::MostBorrowed,

            $this->containsAny($normalized, ['quien tiene', 'quienes tienen', 'a quien', 'asignado a', 'en poder de'])
                || ($operation === 'asignacion' && $this->containsAny($normalized, ['activ', 'pendient', 'sin devolver'])) =>
                ChatbotIntent::ActiveLoans,

            $this->containsAny($normalized, ['bajo stock', 'stock bajo', 'por agotarse', 'casi agotad', 'stock minimo', 'faltante']) =>
                ChatbotIntent::LowStock,

            $this->containsAny($normalized, ['resumen inventario', 'resumen del inventario', 'estado inventario', 'estado del inventario', 'total inventario', 'total del inventario', 'como esta el inventario', 'panorama inventario', 'panorama del inventario']) =>
                ChatbotIntent::InventorySummary,

            $this->isUnitsQuestion($normalized) =>
                ChatbotIntent::Units,

            $this->containsAny($normalized, ['incidenc', 'novedad', 'accidente', 'dano reportad']) =>
                ChatbotIntent::Incidents,

            $operation !== null =>
                ChatbotIntent::OperationSummary,

            $status === 'en_mantenimiento'
                && ! $this->containsAny($normalized, ['cuant', 'stock', 'serie', 'codigo']) =>
                ChatbotIntent::ArticlesInMaintenance,

            $status !== null
                || $this->containsAny($normalized, ['disponib', 'stock', 'existencia', 'quedan', 'inventario', 'cuant', 'hay']) =>
                ChatbotIntent::ArticleAvailability,

            default => ChatbotIntent::Unknown,
        };

        return new ChatbotIntentResult(
            $intent,
            trim($message),
            $normalized,
            $from,
            $to,
            $status,
            $operation,
        );
    }

    public function normalize(string $message): string
    {
        return Str::of($message)
            ->lower()
            ->ascii()
            ->replaceMatches('/[^a-z0-9\s]/', ' ')
            ->replaceMatches('/\s+/', ' ')
            ->trim()
            ->toString();
    }

    private function canonicalize(string $message): string
    {
        $vocabulary = [
            'mantenimiento',
            'mantenimientos',
            'devolucion',
            'devoluciones',
            'pendiente',
            'pendientes',
            'disponible',
            'disponibles',
            'asignado',
            'asignados',
            'inoperativo',
            'inoperativos',
            'incidencia',
            'incidencias',
            'inventario',
            'prestamo',
            'prestamos',
            'prestado',
            'prestados',
            'articulo',
            'articulos',
            'unidades',
        ];

        return collect(explode(' ', $message))
            ->map(function (string $token) use ($vocabulary): string {
                if (mb_strlen($token) < 6 || in_array($token, $vocabulary, true)) {
                    return $token;
                }

                $closest = null;
                $distance = PHP_INT_MAX;

                foreach ($vocabulary as $candidate) {
                    $current = levenshtein($token, $candidate);

                    if ($current < $distance) {
                        $distance = $current;
                        $closest = $candidate;
                    }
                }

                return $distance <= 2 ? $closest : $token;
            })
            ->implode(' ');
    }

    /**
     * @return array{0: ?CarbonImmutable, 1: ?CarbonImmutable}
     */
    private function period(string $message): array
    {
        $now = CarbonImmutable::now();

        if ($this->containsAny($message, ['hoy', 'dia actual'])) {
            return [$now->startOfDay(), $now->endOfDay()];
        }

        if ($this->containsAny($message, ['ayer', 'dia anterior'])) {
            $yesterday = $now->subDay();

            return [$yesterday->startOfDay(), $yesterday->endOfDay()];
        }

        if (preg_match('/ultim(?:os|as)?\s+(\d+)\s+dias?/', $message, $matches)) {
            return [$now->subDays(max(1, (int) $matches[1]))->startOfDay(), $now->endOfDay()];
        }

        if ($this->containsAny($message, ['esta semana', 'semana actual'])) {
            return [$now->startOfWeek(), $now->endOfWeek()];
        }

        if ($this->containsAny($message, ['semana pasada', 'ultima semana'])) {
            $previous = $now->subWeek();

            return [$previous->startOfWeek(), $previous->endOfWeek()];
        }

        if ($this->containsAny($message, ['mes pasado', 'ultimo mes'])) {
            $previous = $now->subMonth();

            return [$previous->startOfMonth(), $previous->endOfMonth()];
        }

        if ($this->containsAny($message, ['este mes', 'mes actual'])) {
            return [$now->startOfMonth(), $now->endOfMonth()];
        }

        if ($this->containsAny($message, ['este ano', 'ano actual'])) {
            return [$now->startOfYear(), $now->endOfYear()];
        }

        return [null, null];
    }

    private function status(string $message): ?string
    {
        return match (true) {
            $this->containsAny($message, ['en mantenimiento', 'mantenimiento']) => 'en_mantenimiento',
            $this->containsAny($message, ['inoperativ', 'fuera de servicio', 'no operativ']) => 'inoperativo',
            $this->containsAny($message, ['perdid', 'extraviad']) => 'perdido',
            $this->containsAny($message, ['robad', 'sustraido']) => 'robado',
            $this->containsAny($message, ['dado de baja', 'dados de baja', 'baja definitiva']) => 'dado_de_baja',
            $this->containsAny($message, ['asignad', 'prestado', 'entregado', 'en uso']) => 'asignado',
            $this->containsAny($message, ['disponib', 'libre', 'en stock', 'existencia']) => 'disponible',
            default => null,
        };
    }

    private function operation(string $message): ?string
    {
        return match (true) {
            $this->containsAny($message, ['devolucion', 'devoluciones', 'devuelt', 'retorn']) => 'devolucion',
            $this->containsAny($message, ['prestamo', 'prestamos', 'asignacion', 'asignaciones', 'entrega', 'entregas']) => 'asignacion',
            $this->containsAny($message, ['consumo', 'consumos', 'consumid']) => 'consumo',
            $this->containsAny($message, ['salida mantenimiento']) => 'mantenimiento_salida',
            $this->containsAny($message, ['retorno mantenimiento']) => 'mantenimiento_retorno',
            $this->containsAny($message, ['ajuste', 'ajustes']) => 'ajuste',
            default => null,
        };
    }

    private function isUnitsQuestion(string $message): bool
    {
        if (! preg_match('/\b(unidad|unidades|dependencia|dependencias|reparticion|reparticiones)\b/', $message)) {
            return false;
        }

        if ($this->containsAny($message, [
            'inventario', 'stock', 'disponib', 'asignad', 'mantenimiento', 'articulo',
            'equipo', 'armamento', 'serie', 'consumible', 'municion',
        ])) {
            return false;
        }

        return $this->containsAny($message, [
            'cuant', 'total', 'hay', 'lista', 'listar', 'mostrar', 'registrad',
            'exist', 'cuales', 'nombres', 'dependencia', 'reparticion',
        ]);
    }

    private function containsAny(string $message, array $needles): bool
    {
        foreach ($needles as $needle) {
            if (str_contains($message, $needle)) {
                return true;
            }
        }

        return false;
    }

}
