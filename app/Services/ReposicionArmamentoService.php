<?php

namespace App\Services;

use App\Models\Articulo;
use Illuminate\Support\Carbon;
use Illuminate\Support\Collection;

class ReposicionArmamentoService
{
    public function resumenGeneral(): array
    {
        $items = $this->recomendaciones();

        return [
            'articulos_evaluados' => $items->count(),
            'reposicion_inmediata' => $items->where('urgencia', 'inmediata')->count(),
            'reposicion_proxima' => $items->where('urgencia', 'proxima')->count(),
            'cantidad_sugerida_total' => (int) $items->sum('cantidad_sugerida'),
        ];
    }

    public function recomendaciones(): Collection
    {
        $now = now();
        $since90 = $now->copy()->subDays(90);
        $since180 = $now->copy()->subDays(180);

        return Articulo::query()
            ->where('tipo', 'reutilizable')
            ->where('seguimiento', 'serie')
            ->whereNull('deleted_at')
            ->with('categoria')
            ->withCount([
                'series as total_series' => fn ($query) => $query->whereNull('deleted_at'),
                'series as operativas' => fn ($query) => $query
                    ->whereNull('deleted_at')
                    ->whereNotIn('estado', ['inoperativo'])
                    ->whereNotIn('condicion_actual', ['inoperativo', 'malo']),
                'series as observadas' => fn ($query) => $query
                    ->whereNull('deleted_at')
                    ->where(function ($builder) {
                        $builder
                            ->whereIn('condicion_actual', ['con_defectos', 'malo'])
                            ->orWhereIn('estado', ['en_mantenimiento']);
                    })
                    ->whereNotIn('estado', ['inoperativo'])
                    ->whereNotIn('condicion_actual', ['inoperativo']),
                'series as inoperativas' => fn ($query) => $query
                    ->whereNull('deleted_at')
                    ->where(function ($builder) {
                        $builder
                            ->where('estado', 'inoperativo')
                            ->orWhere('condicion_actual', 'inoperativo');
                    }),
                'series as mantenimientos_abiertos' => fn ($query) => $query
                    ->whereNull('deleted_at')
                    ->whereHas('mantenimientos', fn ($maintenance) => $maintenance
                        ->whereNull('deleted_at')
                        ->whereNull('fecha_fin')),
                'incidencias as incidencias_90d' => fn ($query) => $query
                    ->whereNull('deleted_at')
                    ->where('fecha', '>=', $since90),
                'inspecciones as inspecciones_observadas_90d' => fn ($query) => $query
                    ->whereNull('deleted_at')
                    ->where('realizada_en', '>=', $since90)
                    ->whereIn('resultado', ['observado', 'inoperativo']),
                'mantenimientos as mantenimientos_180d' => fn ($query) => $query
                    ->whereNull('deleted_at')
                    ->where(function ($builder) use ($since180) {
                        $builder
                            ->where('fecha_inicio', '>=', $since180)
                            ->orWhere('created_at', '>=', $since180);
                    }),
            ])
            ->orderBy('nombre')
            ->get()
            ->filter(fn (Articulo $articulo) => $articulo->total_series > 0)
            ->map(function (Articulo $articulo) use ($now) {
                $total = max(1, (int) $articulo->total_series);
                $inoperativas = (int) $articulo->inoperativas;
                $observadas = (int) $articulo->observadas;
                $operativas = (int) $articulo->operativas;
                $incidencias90d = (int) $articulo->incidencias_90d;
                $inspecciones90d = (int) $articulo->inspecciones_observadas_90d;
                $mantenimientosAbiertos = (int) $articulo->mantenimientos_abiertos;
                $mantenimientos180d = (int) $articulo->mantenimientos_180d;

                $inoperativaRatio = $inoperativas / $total;
                $observadaRatio = $observadas / $total;
                $incidenciaRatio = $incidencias90d / $total;
                $inspeccionRatio = $inspecciones90d / $total;
                $mantenimientoAbiertoRatio = $mantenimientosAbiertos / $total;

                $score = 0;
                $score += $inoperativaRatio * 55;
                $score += $observadaRatio * 20;
                $score += min($incidenciaRatio, 1.5) * 12;
                $score += min($inspeccionRatio, 1.0) * 8;
                $score += min($mantenimientoAbiertoRatio, 1.0) * 10;
                $score += $mantenimientos180d >= max(1, (int) ceil($total * 0.4)) ? 5 : 0;
                $score = round(min($score, 100), 2);

                $cantidadSugerida = (int) ceil($inoperativas + ($observadas * 0.5));
                $cantidadSugerida = $score >= 35 ? max(1, $cantidadSugerida) : $cantidadSugerida;

                [$urgencia, $diasMinimos, $diasMaximos] = match (true) {
                    $score >= 60 => ['inmediata', 0, 30],
                    $score >= 40 => ['proxima', 30, 60],
                    $score >= 25 => ['planificada', 60, 90],
                    default => ['estable', 120, 180],
                };

                $fechaReferencia = $now->copy()->addDays($diasMinimos)->toDateString();

                return [
                    'articulo_id' => $articulo->id,
                    'articulo' => $articulo->nombre,
                    'categoria' => $articulo->categoria?->nombre,
                    'total_series' => $total,
                    'operativas' => $operativas,
                    'observadas' => $observadas,
                    'inoperativas' => $inoperativas,
                    'incidencias_90d' => $incidencias90d,
                    'inspecciones_observadas_90d' => $inspecciones90d,
                    'mantenimientos_abiertos' => $mantenimientosAbiertos,
                    'mantenimientos_180d' => $mantenimientos180d,
                    'salud_operativa' => round(($operativas / $total) * 100, 2),
                    'score_reposicion' => $score,
                    'urgencia' => $urgencia,
                    'dias_recomendados_min' => $diasMinimos,
                    'dias_recomendados_max' => $diasMaximos,
                    'fecha_sugerida_desde' => $fechaReferencia,
                    'cantidad_sugerida' => $cantidadSugerida,
                    'motivo' => $this->buildReason(
                        inoperativas: $inoperativas,
                        observadas: $observadas,
                        incidencias90d: $incidencias90d,
                        mantenimientosAbiertos: $mantenimientosAbiertos,
                        total: $total,
                    ),
                ];
            })
            ->sortByDesc('score_reposicion')
            ->values();
    }

    protected function buildReason(
        int $inoperativas,
        int $observadas,
        int $incidencias90d,
        int $mantenimientosAbiertos,
        int $total,
    ): string {
        $fragments = [];

        if ($inoperativas > 0) {
            $fragments[] = "{$inoperativas} de {$total} series ya estan inoperativas";
        }

        if ($observadas > 0) {
            $fragments[] = "{$observadas} series presentan desgaste o mantenimiento";
        }

        if ($incidencias90d > 0) {
            $fragments[] = "{$incidencias90d} incidencias registradas en 90 dias";
        }

        if ($mantenimientosAbiertos > 0) {
            $fragments[] = "{$mantenimientosAbiertos} series siguen en mantenimiento abierto";
        }

        if ($fragments === []) {
            return 'El armamento se mantiene estable; por ahora solo se recomienda seguimiento preventivo.';
        }

        return ucfirst(implode(', ', $fragments)).'.';
    }
}
