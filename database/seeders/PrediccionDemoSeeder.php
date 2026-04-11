<?php

namespace Database\Seeders;

use App\Models\ArticuloSerie;
use App\Models\Inspeccion;
use App\Models\Mantenimiento;
use App\Models\Operacion;
use App\Models\OperacionDetalle;
use App\Models\OperacionDetalleSerie;
use App\Models\TipoIncidente;
use App\Models\Unidad;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

class PrediccionDemoSeeder extends Seeder
{
    private const MARKER = 'ML-DEMO';

    public function run(): void
    {
        if (Inspeccion::query()->where('observaciones', 'like', self::MARKER.'%')->exists()) {
            return;
        }

        $tiposIncidente = TipoIncidente::query()->orderBy('id')->get();

        foreach (Unidad::query()->orderBy('id')->get() as $unidad) {
            $actor = User::query()
                ->whereIn('role', ['administrador_unidad', 'furriel'])
                ->where('unidad_id', $unidad->id)
                ->orderBy('id')
                ->first();

            $policias = User::query()
                ->where('role', 'policia')
                ->where('unidad_id', $unidad->id)
                ->orderBy('id')
                ->get();

            $series = ArticuloSerie::query()
                ->where('unidad_id', $unidad->id)
                ->whereNull('deleted_at')
                ->with('articulo')
                ->orderBy('id')
                ->get();

            if (! $actor || $policias->isEmpty() || $series->isEmpty()) {
                continue;
            }

            DB::transaction(function () use ($unidad, $actor, $policias, $series, $tiposIncidente) {
                $this->seedSeriesProfiles($unidad->id, $actor, $policias, $series, $tiposIncidente);
            });
        }
    }

    private function seedSeriesProfiles(
        int $unidadId,
        User $actor,
        Collection $policias,
        Collection $series,
        Collection $tiposIncidente
    ): void {
        $profiles = [
            'estable',
            'estable',
            'observado',
            'observado',
            'mantenimiento',
            'inoperativo',
        ];

        foreach ($series->values() as $index => $serie) {
            $profile = $profiles[$index % count($profiles)];
            $policia = $policias[$index % $policias->count()];

            $this->createHistoricOperations($unidadId, $actor, $policia, $serie, $index);
            $this->createInspectionHistory($actor, $serie, $profile, $index);
            $this->createMaintenanceHistory($actor, $serie, $profile, $index);
            $this->createIncidenceHistory($actor, $policia, $serie, $tiposIncidente, $profile, $index);
            $this->applyCurrentState($serie, $profile);
        }
    }

    private function createHistoricOperations(int $unidadId, User $actor, User $policia, ArticuloSerie $serie, int $offset): void
    {
        $articulo = $serie->articulo;

        $asignacion = Operacion::query()->firstOrCreate(
            ['observaciones' => self::MARKER." asignacion base serie {$serie->id}"],
            [
                'tipo' => 'asignacion',
                'evento_id' => null,
                'usuario_destino_id' => $policia->id,
                'actor_id' => $actor->id,
                'unidad_id' => $unidadId,
                'fecha' => now()->subDays(100 - ($offset % 20)),
            ]
        );

        $detalleAsignacion = OperacionDetalle::query()->firstOrCreate(
            [
                'operacion_id' => $asignacion->id,
                'articulo_id' => $articulo->id,
            ],
            [
                'cantidad' => 1,
                'condicion' => 'bueno',
                'observaciones' => self::MARKER.' detalle asignacion base',
            ]
        );

        OperacionDetalleSerie::query()->firstOrCreate(
            [
                'operacion_detalle_id' => $detalleAsignacion->id,
                'serie_id' => $serie->id,
            ]
        );

        $devolucion = Operacion::query()->firstOrCreate(
            ['observaciones' => self::MARKER." devolucion base serie {$serie->id}"],
            [
                'tipo' => 'devolucion',
                'evento_id' => null,
                'usuario_destino_id' => $policia->id,
                'actor_id' => $actor->id,
                'unidad_id' => $unidadId,
                'fecha' => now()->subDays(70 - ($offset % 15)),
                'operacion_padre_id' => $asignacion->id,
            ]
        );

        $detalleDevolucion = OperacionDetalle::query()->firstOrCreate(
            [
                'operacion_id' => $devolucion->id,
                'articulo_id' => $articulo->id,
            ],
            [
                'cantidad' => 1,
                'condicion' => 'bueno',
                'observaciones' => self::MARKER.' detalle devolucion base',
            ]
        );

        OperacionDetalleSerie::query()->firstOrCreate(
            [
                'operacion_detalle_id' => $detalleDevolucion->id,
                'serie_id' => $serie->id,
            ]
        );

        if ($offset % 2 === 0) {
            $segundaAsignacion = Operacion::query()->firstOrCreate(
                ['observaciones' => self::MARKER." asignacion reciente serie {$serie->id}"],
                [
                    'tipo' => 'asignacion',
                    'evento_id' => null,
                    'usuario_destino_id' => $policia->id,
                    'actor_id' => $actor->id,
                    'unidad_id' => $unidadId,
                    'fecha' => now()->subDays(18 - ($offset % 5)),
                ]
            );

            $detalleReciente = OperacionDetalle::query()->firstOrCreate(
                [
                    'operacion_id' => $segundaAsignacion->id,
                    'articulo_id' => $articulo->id,
                ],
                [
                    'cantidad' => 1,
                    'condicion' => 'regular',
                    'observaciones' => self::MARKER.' detalle asignacion reciente',
                ]
            );

            OperacionDetalleSerie::query()->firstOrCreate(
                [
                    'operacion_detalle_id' => $detalleReciente->id,
                    'serie_id' => $serie->id,
                ]
            );
        }
    }

    private function createInspectionHistory(User $actor, ArticuloSerie $serie, string $profile, int $offset): void
    {
        $history = match ($profile) {
            'estable' => [
                ['resultado' => 'apto', 'days' => 85, 'observacion' => 'revision satisfactoria'],
                ['resultado' => 'apto', 'days' => 20, 'observacion' => 'sin novedades'],
            ],
            'observado' => [
                ['resultado' => 'apto', 'days' => 95, 'observacion' => 'uso normal'],
                ['resultado' => 'observado', 'days' => 12, 'observacion' => 'presenta desgaste visible'],
            ],
            'mantenimiento' => [
                ['resultado' => 'observado', 'days' => 60, 'observacion' => 'requiere mantenimiento preventivo'],
                ['resultado' => 'observado', 'days' => 8, 'observacion' => 'persisten observaciones'],
            ],
            'inoperativo' => [
                ['resultado' => 'observado', 'days' => 50, 'observacion' => 'deterioro severo'],
                ['resultado' => 'inoperativo', 'days' => 3, 'observacion' => 'fuera de servicio'],
            ],
        };

        foreach ($history as $item) {
            $observacion = self::MARKER." inspeccion serie {$serie->id} {$item['days']} {$item['observacion']}";

            Inspeccion::query()->updateOrCreate(
                ['observaciones' => $observacion],
                [
                    'articulo_id' => $serie->articulo_id,
                    'serie_id' => $serie->id,
                    'inspector_id' => $actor->id,
                    'resultado' => $item['resultado'],
                    'realizada_en' => now()->subDays($item['days'] + ($offset % 4)),
                ]
            );
        }
    }

    private function createMaintenanceHistory(User $actor, ArticuloSerie $serie, string $profile, int $offset): void
    {
        $items = match ($profile) {
            'estable' => [
                ['tipo' => 'preventivo', 'inicio' => 140, 'fin' => 138, 'costo' => 80, 'descripcion' => 'mantenimiento preventivo completado'],
            ],
            'observado' => [
                ['tipo' => 'preventivo', 'inicio' => 170, 'fin' => 168, 'costo' => 90, 'descripcion' => 'mantenimiento preventivo antiguo'],
            ],
            'mantenimiento' => [
                ['tipo' => 'preventivo', 'inicio' => 35, 'fin' => 33, 'costo' => 110, 'descripcion' => 'ingreso reciente a mantenimiento'],
            ],
            'inoperativo' => [
                ['tipo' => 'correctivo', 'inicio' => 15, 'fin' => null, 'costo' => 240, 'descripcion' => 'mantenimiento correctivo inconcluso'],
            ],
        };

        foreach ($items as $item) {
            $descripcion = self::MARKER." mantenimiento serie {$serie->id} {$item['inicio']} {$item['descripcion']}";

            Mantenimiento::query()->updateOrCreate(
                ['descripcion' => $descripcion],
                [
                    'articulo_id' => $serie->articulo_id,
                    'serie_id' => $serie->id,
                    'created_por' => $actor->id,
                    'tipo' => $item['tipo'],
                    'fecha_inicio' => now()->subDays($item['inicio'] + ($offset % 5)),
                    'fecha_fin' => $item['fin'] !== null ? now()->subDays($item['fin'] + ($offset % 5)) : null,
                    'costo' => $item['costo'],
                ]
            );
        }
    }

    private function createIncidenceHistory(
        User $actor,
        User $policia,
        ArticuloSerie $serie,
        Collection $tiposIncidente,
        string $profile,
        int $offset
    ): void {
        $incidencias = match ($profile) {
            'estable' => [],
            'observado' => [
                ['tipo' => 0, 'days' => 14, 'descripcion' => 'reporte menor por desgaste'],
            ],
            'mantenimiento' => [
                ['tipo' => 2, 'days' => 10, 'descripcion' => 'golpe operativo con afectacion parcial'],
                ['tipo' => 0, 'days' => 5, 'descripcion' => 'devolucion retrasada para revision'],
            ],
            'inoperativo' => [
                ['tipo' => 2, 'days' => 22, 'descripcion' => 'danio estructural durante operativo'],
                ['tipo' => 1, 'days' => 6, 'descripcion' => 'pieza extraviada en intervencion'],
            ],
        };

        foreach ($incidencias as $item) {
            $tipo = $tiposIncidente[$item['tipo'] % max($tiposIncidente->count(), 1)] ?? $tiposIncidente->first();

            if (! $tipo) {
                continue;
            }

            $descripcion = self::MARKER." incidencia serie {$serie->id} {$item['days']} {$item['descripcion']}";

            DB::table('incidencias')->updateOrInsert(
                ['descripcion' => $descripcion],
                [
                    'tipo_id' => $tipo->id,
                    'articulo_id' => $serie->articulo_id,
                    'serie_id' => $serie->id,
                    'policia_id' => $policia->id,
                    'fecha' => now()->subDays($item['days'] + ($offset % 6)),
                    'created_por' => $actor->id,
                    'created_at' => now(),
                    'updated_at' => now(),
                ]
            );
        }
    }

    private function applyCurrentState(ArticuloSerie $serie, string $profile): void
    {
        $state = match ($profile) {
            'estable' => ['estado' => 'disponible', 'condicion_actual' => 'bueno', 'operacion_detalle_id_actual' => null],
            'observado' => ['estado' => 'disponible', 'condicion_actual' => 'con_defectos', 'operacion_detalle_id_actual' => null],
            'mantenimiento' => ['estado' => 'en_mantenimiento', 'condicion_actual' => 'malo', 'operacion_detalle_id_actual' => null],
            'inoperativo' => ['estado' => 'inoperativo', 'condicion_actual' => 'inoperativo', 'operacion_detalle_id_actual' => null],
        };

        $serie->forceFill($state)->save();
    }
}
