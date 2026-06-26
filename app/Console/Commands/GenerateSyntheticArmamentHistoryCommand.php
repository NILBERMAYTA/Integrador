<?php

namespace App\Console\Commands;

use App\Models\Unidad;
use App\Services\SyntheticArmamentHistoryPlanner;
use Illuminate\Console\Command;
use Illuminate\Database\Query\Builder;
use Carbon\CarbonInterface;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use RuntimeException;

class GenerateSyntheticArmamentHistoryCommand extends Command
{
    protected $signature = 'simulation:generate-armament-history
        {--coverage=75 : Porcentaje de series de cada unidad con historial}
        {--min-inspections=4 : Mínimo de inspecciones por serie}
        {--max-inspections=7 : Máximo de inspecciones por serie}
        {--months=24 : Ventana histórica en meses}
        {--seed=20260622 : Semilla determinista}
        {--unit=* : ID o código externo de una unidad específica}
        {--only-imported : Solo unidades importadas desde data_migration}
        {--replace : Elimina y regenera únicamente los datos creados por este comando}
        {--dry-run : Muestra el plan sin escribir en la base de datos}
        {--chunk=1000 : Tamaño de los lotes de inserción}';

    protected $description = 'Genera historia sintética coherente sin modificar usuarios, unidades, artículos ni series';

    private const MARKER = 'SIM-HIST-v1';

    public function __construct(
        private readonly SyntheticArmamentHistoryPlanner $planner,
    ) {
        parent::__construct();
    }

    public function handle(): int
    {
        [$coverage, $minInspections, $maxInspections, $months, $seed, $chunk] =
            $this->validatedOptions();
        $units = $this->units();

        if ($units->isEmpty()) {
            $this->error('No se encontraron unidades para el alcance solicitado.');

            return self::FAILURE;
        }

        $this->info('Generador de historial sintético coherente');
        $this->line('Marca: '.self::MARKER);
        $this->line("Unidades: {$units->count()} · cobertura: {$coverage}% · ventana: {$months} meses");

        if ((bool) $this->option('replace') && ! (bool) $this->option('dry-run')) {
            $this->warn('Se eliminarán solamente registros identificados con '.self::MARKER.'.');
            $this->deleteGeneratedHistory($units->pluck('id')->map(fn ($id) => (int) $id)->all());
        }

        $summary = [];
        foreach ($units as $unit) {
            $unitSummary = $this->generateUnit(
                $unit,
                $coverage,
                $minInspections,
                $maxInspections,
                $months,
                $seed,
                $chunk,
                (bool) $this->option('dry-run'),
            );
            $summary[] = $unitSummary;
            $this->line(sprintf(
                '%s: %d/%d series, %d inspecciones, %d operaciones, %d mantenimientos, %d incidencias%s',
                $unit->nombre,
                $unitSummary['series_generadas'],
                $unitSummary['series_totales'],
                $unitSummary['inspecciones'],
                $unitSummary['operaciones'],
                $unitSummary['mantenimientos'],
                $unitSummary['incidencias'],
                $unitSummary['omitidas'] > 0 ? " ({$unitSummary['omitidas']} ya existentes)" : '',
            ));
        }

        $totals = collect($summary);
        $this->newLine();
        $this->table(
            ['Unidades', 'Series', 'Inspecciones', 'Operaciones', 'Mantenimientos', 'Incidencias'],
            [[
                $units->count(),
                number_format((int) $totals->sum('series_generadas'), 0, ',', '.'),
                number_format((int) $totals->sum('inspecciones'), 0, ',', '.'),
                number_format((int) $totals->sum('operaciones'), 0, ',', '.'),
                number_format((int) $totals->sum('mantenimientos'), 0, ',', '.'),
                number_format((int) $totals->sum('incidencias'), 0, ',', '.'),
            ]],
        );

        $this->info(
            (bool) $this->option('dry-run')
                ? 'Simulación terminada: la base de datos no fue modificada.'
                : 'Historial sintético generado sin alterar los registros maestros.',
        );

        return self::SUCCESS;
    }

    private function generateUnit(
        Unidad $unit,
        int $coverage,
        int $minInspections,
        int $maxInspections,
        int $months,
        int $seed,
        int $chunk,
        bool $dryRun,
    ): array {
        $series = DB::table('articulo_series')
            ->where('unidad_id', $unit->id)
            ->whereNull('deleted_at')
            ->orderBy('id')
            ->get(['id', 'articulo_id', 'codigo_serie', 'estado', 'condicion_actual']);
        $target = $coverage === 0
            ? 0
            : max(1, (int) ceil($series->count() * ($coverage / 100)));
        $selected = $series
            ->sortBy(fn ($serie) => $this->planner->selectionScore(
                (int) $serie->id,
                (string) $serie->codigo_serie,
                $seed,
            ))
            ->take($target)
            ->values();
        $existing = DB::table('inspecciones')
            ->whereIn('serie_id', $selected->pluck('id'))
            ->where('observaciones', 'like', self::MARKER.'|%')
            ->pluck('serie_id')
            ->map(fn ($id) => (int) $id)
            ->flip();
        $pending = $selected
            ->reject(fn ($serie) => $existing->has((int) $serie->id))
            ->values();
        [$actorId, $policeIds] = $this->unitUsers((int) $unit->id);
        $incidentTypeId = $this->damagedIncidentTypeId();
        $counts = [
            'series_totales' => $series->count(),
            'series_generadas' => $pending->count(),
            'omitidas' => $selected->count() - $pending->count(),
            'inspecciones' => 0,
            'operaciones' => 0,
            'mantenimientos' => 0,
            'incidencias' => 0,
        ];

        foreach ($pending->chunk(250) as $seriesChunk) {
            $plans = [];
            foreach ($seriesChunk as $serie) {
                $serieArray = (array) $serie;
                $plan = $this->planner->plan(
                    $serieArray,
                    $seed,
                    $minInspections,
                    $maxInspections,
                    $months,
                );
                $policeId = $policeIds[
                    $this->planner->selectionScore(
                        (int) $serie->id,
                        (string) $serie->codigo_serie,
                        $seed + 1,
                    ) % count($policeIds)
                ];
                $counts['inspecciones'] += count($plan['inspections']);
                $counts['operaciones'] += count($plan['operations']) * 2;
                $counts['mantenimientos'] += count($plan['maintenances']);
                $counts['incidencias'] += count($plan['incidents']);

                if (! $dryRun) {
                    $plans[] = compact('serieArray', 'plan', 'policeId');
                }
            }

            if ($plans !== []) {
                $this->persistPlans(
                    $plans,
                    $actorId,
                    $incidentTypeId,
                    (int) $unit->id,
                    $chunk,
                );
            }
        }

        return $counts;
    }

    private function persistPlans(
        array $plans,
        int $actorId,
        int $incidentTypeId,
        int $unitId,
        int $chunk,
    ): void {
        DB::transaction(function () use (
            $plans,
            $actorId,
            $incidentTypeId,
            $unitId,
            $chunk,
        ): void {
            $inspectionRows = [];
            $maintenanceRows = [];
            $incidentRows = [];
            $operationGroups = [];
            $now = now();

            foreach ($plans as $item) {
                $serie = $item['serieArray'];
                $plan = $item['plan'];
                $policeId = $item['policeId'];
                $profile = $plan['profile'];

                foreach ($plan['inspections'] as $inspection) {
                    $inspectionRows[] = [
                        'articulo_id' => $serie['articulo_id'],
                        'serie_id' => $serie['id'],
                        'inspector_id' => $actorId,
                        'resultado' => $inspection['resultado'],
                        'observaciones' => self::MARKER
                            ."|serie:{$serie['id']}|inspeccion:{$inspection['sequence']}|perfil:{$profile}",
                        'realizada_en' => $inspection['fecha'],
                        'created_at' => $inspection['fecha'],
                        'updated_at' => $inspection['fecha'],
                        'deleted_at' => null,
                    ];
                }

                foreach ($plan['maintenances'] as $maintenance) {
                    $maintenanceRows[] = [
                        'articulo_id' => $serie['articulo_id'],
                        'serie_id' => $serie['id'],
                        'created_por' => $actorId,
                        'tipo' => $maintenance['tipo'],
                        'descripcion' => self::MARKER
                            ."|serie:{$serie['id']}|mantenimiento:{$maintenance['sequence']}|perfil:{$profile}",
                        'fecha_inicio' => $maintenance['fecha_inicio'],
                        'fecha_fin' => $maintenance['fecha_fin'],
                        'costo' => $maintenance['costo'],
                        'created_at' => $maintenance['fecha_inicio'],
                        'updated_at' => $maintenance['fecha_fin'] ?? $now,
                        'deleted_at' => null,
                    ];
                }

                foreach ($plan['incidents'] as $incident) {
                    $incidentRows[] = [
                        'tipo_id' => $incidentTypeId,
                        'articulo_id' => $serie['articulo_id'],
                        'serie_id' => $serie['id'],
                        'policia_id' => $policeId,
                        'descripcion' => self::MARKER
                            ."|serie:{$serie['id']}|incidencia:{$incident['sequence']}|severidad:{$incident['severity']}|perfil:{$profile}",
                        'fecha' => $incident['fecha'],
                        'created_por' => $actorId,
                        'created_at' => $incident['fecha'],
                        'updated_at' => $incident['fecha'],
                        'deleted_at' => null,
                    ];
                }

                foreach ($plan['operations'] as $operation) {
                    $key = implode('|', [
                        $serie['articulo_id'],
                        $policeId,
                        $operation['asignacion']->toDateString(),
                        $operation['devolucion']->toDateString(),
                        $operation['condicion_devolucion'],
                    ]);
                    $operationGroups[$key] ??= [
                        'articulo_id' => (int) $serie['articulo_id'],
                        'police_id' => $policeId,
                        'asignacion' => $operation['asignacion'],
                        'devolucion' => $operation['devolucion'],
                        'condicion_devolucion' => $operation['condicion_devolucion'],
                        'series' => [],
                    ];
                    $operationGroups[$key]['series'][] = (int) $serie['id'];
                }
            }

            $this->insertRows('inspecciones', $inspectionRows, $chunk);
            $this->insertRows('mantenimientos', $maintenanceRows, $chunk);
            $this->insertRows('incidencias', $incidentRows, $chunk);
            $this->insertOperationGroups(
                $unitId,
                $actorId,
                $operationGroups,
                $chunk,
            );
        });
    }

    private function insertOperationGroups(
        int $unitId,
        int $actorId,
        array $groups,
        int $chunk,
    ): void {
        foreach ($groups as $key => $group) {
            $hash = substr(hash('sha256', $key), 0, 16);
            $assignmentId = DB::table('operaciones')->insertGetId([
                'tipo' => 'asignacion',
                'evento_id' => null,
                'usuario_destino_id' => $group['police_id'],
                'actor_id' => $actorId,
                'unidad_id' => $unitId,
                'fecha' => $group['asignacion'],
                'observaciones' => self::MARKER."|operacion:{$hash}|asignacion",
                'operacion_padre_id' => null,
                'created_at' => $group['asignacion'],
                'updated_at' => $group['asignacion'],
                'deleted_at' => null,
            ]);
            $assignmentDetailId = DB::table('operacion_detalles')->insertGetId([
                'operacion_id' => $assignmentId,
                'articulo_id' => $group['articulo_id'],
                'cantidad' => count($group['series']),
                'condicion' => 'bueno',
                'observaciones' => self::MARKER."|detalle:{$hash}|asignacion",
                'created_at' => $group['asignacion'],
                'updated_at' => $group['asignacion'],
                'deleted_at' => null,
            ]);
            $this->insertSeriesLinks(
                $assignmentDetailId,
                $group['series'],
                $group['asignacion'],
                $chunk,
            );

            $returnId = DB::table('operaciones')->insertGetId([
                'tipo' => 'devolucion',
                'evento_id' => null,
                'usuario_destino_id' => $group['police_id'],
                'actor_id' => $actorId,
                'unidad_id' => $unitId,
                'fecha' => $group['devolucion'],
                'observaciones' => self::MARKER."|operacion:{$hash}|devolucion",
                'operacion_padre_id' => $assignmentId,
                'created_at' => $group['devolucion'],
                'updated_at' => $group['devolucion'],
                'deleted_at' => null,
            ]);
            $returnDetailId = DB::table('operacion_detalles')->insertGetId([
                'operacion_id' => $returnId,
                'articulo_id' => $group['articulo_id'],
                'cantidad' => count($group['series']),
                'condicion' => $group['condicion_devolucion'],
                'observaciones' => self::MARKER."|detalle:{$hash}|devolucion",
                'created_at' => $group['devolucion'],
                'updated_at' => $group['devolucion'],
                'deleted_at' => null,
            ]);
            $this->insertSeriesLinks(
                $returnDetailId,
                $group['series'],
                $group['devolucion'],
                $chunk,
            );
        }
    }

    private function insertSeriesLinks(
        int $detailId,
        array $series,
        CarbonInterface $date,
        int $chunk,
    ): void {
        $rows = array_map(fn (int $serieId) => [
            'operacion_detalle_id' => $detailId,
            'serie_id' => $serieId,
            'created_at' => $date,
            'updated_at' => $date,
            'deleted_at' => null,
        ], $series);
        $this->insertRows('operacion_detalle_series', $rows, $chunk);
    }

    private function insertRows(string $table, array $rows, int $chunk): void
    {
        foreach (array_chunk($rows, $chunk) as $rowsChunk) {
            if ($rowsChunk !== []) {
                DB::table($table)->insert($rowsChunk);
            }
        }
    }

    private function unitUsers(int $unitId): array
    {
        $users = DB::table('users')
            ->where('unidad_id', $unitId)
            ->whereNull('deleted_at')
            ->orderByRaw("CASE WHEN role::text IN ('administrador_unidad', 'furriel') THEN 0 ELSE 1 END")
            ->orderBy('id')
            ->get(['id', 'role']);

        if ($users->isEmpty()) {
            throw new RuntimeException("La unidad {$unitId} no tiene usuarios para asociar el historial.");
        }

        $actor = $users->first();
        $policeIds = $users
            ->where('role', 'policia')
            ->take(8)
            ->pluck('id')
            ->map(fn ($id) => (int) $id)
            ->values()
            ->all();

        if ($policeIds === []) {
            $policeIds = [(int) $actor->id];
        }

        return [(int) $actor->id, $policeIds];
    }

    private function damagedIncidentTypeId(): int
    {
        $typeId = DB::table('tipos_incidente')
            ->whereNull('deleted_at')
            ->whereRaw('LOWER(nombre) LIKE ?', ['%dañ%'])
            ->value('id')
            ?? DB::table('tipos_incidente')
                ->whereNull('deleted_at')
                ->orderByDesc(DB::raw("COALESCE(NULLIF(regexp_replace(severidad, '[^0-9]', '', 'g'), ''), '0')::int"))
                ->value('id');

        if (! $typeId) {
            throw new RuntimeException('No existe ningún tipo de incidente para crear el historial.');
        }

        return (int) $typeId;
    }

    private function units(): Collection
    {
        $requested = collect((array) $this->option('unit'))
            ->filter(fn ($value) => $value !== null && $value !== '')
            ->values();

        return Unidad::query()
            ->whereNull('deleted_at')
            ->when(
                (bool) $this->option('only-imported'),
                fn ($query) => $query->whereNotNull('codigo_externo'),
            )
            ->when($requested->isNotEmpty(), function ($query) use ($requested) {
                $ids = $requested->filter(fn ($value) => ctype_digit((string) $value));
                $codes = $requested->reject(fn ($value) => ctype_digit((string) $value));

                $query->where(function ($scope) use ($ids, $codes) {
                    if ($ids->isNotEmpty()) {
                        $scope->whereIn('id', $ids->map(fn ($id) => (int) $id));
                    }
                    if ($codes->isNotEmpty()) {
                        $method = $ids->isNotEmpty() ? 'orWhereIn' : 'whereIn';
                        $scope->{$method}('codigo_externo', $codes);
                    }
                });
            })
            ->whereHas('series')
            ->orderBy('id')
            ->get(['id', 'nombre', 'codigo_externo']);
    }

    private function validatedOptions(): array
    {
        $coverage = (int) $this->option('coverage');
        $minimum = (int) $this->option('min-inspections');
        $maximum = (int) $this->option('max-inspections');
        $months = (int) $this->option('months');
        $seed = (int) $this->option('seed');
        $chunk = (int) $this->option('chunk');

        if ($coverage < 0 || $coverage > 100) {
            throw new RuntimeException('--coverage debe estar entre 0 y 100.');
        }
        if ($minimum < 2 || $maximum < $minimum || $maximum > 12) {
            throw new RuntimeException('Las inspecciones deben cumplir 2 <= mínimo <= máximo <= 12.');
        }
        if ($months < 6 || $months > 60) {
            throw new RuntimeException('--months debe estar entre 6 y 60.');
        }

        return [$coverage, $minimum, $maximum, $months, $seed, max(100, $chunk)];
    }

    private function deleteGeneratedHistory(array $unitIds): void
    {
        DB::transaction(function () use ($unitIds): void {
            $series = fn (): Builder => DB::table('articulo_series')
                ->select('id')
                ->whereIn('unidad_id', $unitIds);
            DB::table('inspecciones')
                ->whereIn('serie_id', $series())
                ->where('observaciones', 'like', self::MARKER.'|%')
                ->delete();
            DB::table('mantenimientos')
                ->whereIn('serie_id', $series())
                ->where('descripcion', 'like', self::MARKER.'|%')
                ->delete();
            DB::table('incidencias')
                ->whereIn('serie_id', $series())
                ->where('descripcion', 'like', self::MARKER.'|%')
                ->delete();

            $operations = fn (): Builder => DB::table('operaciones')
                ->select('id')
                ->whereIn('unidad_id', $unitIds)
                ->where('observaciones', 'like', self::MARKER.'|%');
            $details = fn (): Builder => DB::table('operacion_detalles')
                ->select('id')
                ->whereIn('operacion_id', $operations());
            DB::table('operacion_detalle_series')
                ->whereIn('operacion_detalle_id', $details())
                ->delete();
            DB::table('operacion_detalles')
                ->whereIn('operacion_id', $operations())
                ->delete();
            DB::table('operaciones')
                ->whereIn('id', $operations())
                ->delete();
        });
    }
}
