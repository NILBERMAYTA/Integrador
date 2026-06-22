<?php

namespace App\Console\Commands;

use App\Models\Articulo;
use App\Models\Unidad;
use Illuminate\Console\Command;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

class PopulateTestUnitInventoryCommand extends Command
{
    protected $signature = 'test:populate-unit-inventory
        {--dry-run : Calcula las existencias sin escribir en la base de datos}
        {--only-imported : Limita la carga a unidades provenientes del XLS}
        {--chunk=1000 : Cantidad de series insertadas por lote}';

    protected $description = 'Genera inventario de prueba proporcional al personal registrado por unidad';

    private const GENERATED_MARKER = 'Inventario sintético proporcional al personal';

    public function handle(): int
    {
        $units = Unidad::query()
            ->when(
                (bool) $this->option('only-imported'),
                fn ($query) => $query->whereNotNull('codigo_externo'),
            )
            ->withCount([
                'usuarios as personal_count' => fn ($query) => $query
                    ->whereNull('deleted_at')
                    ->whereNotNull('cedula'),
            ])
            ->orderBy('id')
            ->get(['id', 'nombre', 'sigla', 'codigo_externo']);

        $articles = Articulo::query()
            ->with('categoria:id,nombre')
            ->orderBy('id')
            ->get();

        if ($units->isEmpty() || $articles->isEmpty()) {
            $this->error('No existen unidades o artículos para poblar.');

            return self::FAILURE;
        }

        [$consumableRows, $seriesRows, $summary] = $this->buildRows($units, $articles);

        $this->table(
            ['Concepto', 'Cantidad'],
            [
                ['Unidades', $units->count()],
                ['Artículos consumibles', $articles->where('seguimiento', 'cantidad')->count()],
                ['Filas de inventario', count($consumableRows)],
                ['Artículos serializados', $articles->where('seguimiento', 'serie')->count()],
                ['Series sintéticas', count($seriesRows)],
                ['Personal considerado', $units->sum('personal_count')],
            ],
        );

        $this->newLine();
        $this->table(
            ['Unidad', 'Personal', 'Consumibles', 'Series'],
            collect($summary)
                ->sortByDesc('personal')
                ->take(10)
                ->map(fn ($row) => [
                    $row['unidad'],
                    $row['personal'],
                    number_format($row['consumibles'], 0, ',', '.'),
                    number_format($row['series'], 0, ',', '.'),
                ])
                ->values()
                ->all(),
        );

        if ((bool) $this->option('dry-run')) {
            $this->info('Simulación terminada. No se modificó la base de datos.');

            return self::SUCCESS;
        }

        DB::transaction(function () use ($consumableRows): void {
            DB::table('inventario_unidad_articulos')->upsert(
                $consumableRows,
                ['unidad_id', 'articulo_id'],
                [
                    'cantidad_disponible',
                    'cantidad_asignada',
                    'cantidad_mantenimiento',
                    'stock_minimo',
                    'updated_at',
                ],
            );
        });

        $chunkSize = max(100, (int) $this->option('chunk'));
        $bar = $this->output->createProgressBar(count($seriesRows));
        $bar->start();

        foreach (array_chunk($seriesRows, $chunkSize) as $chunk) {
            DB::table('articulo_series')->upsert(
                $chunk,
                ['codigo_serie'],
                [
                    'articulo_id',
                    'unidad_id',
                    'observaciones',
                    'estado',
                    'condicion_actual',
                    'deleted_at',
                    'updated_at',
                ],
            );
            $bar->advance(count($chunk));
        }

        $bar->finish();
        $this->newLine(2);
        $this->info('Inventario sintético creado correctamente.');

        return self::SUCCESS;
    }

    private function buildRows(Collection $units, Collection $articles): array
    {
        $consumableRows = [];
        $seriesRows = [];
        $summary = [];
        $now = now();

        foreach ($units as $unit) {
            $personnel = max(1, (int) $unit->personal_count);
            $unitCode = $this->unitCode($unit);
            $unitConsumables = 0;
            $unitSeries = 0;

            foreach ($articles as $article) {
                $category = (string) $article->categoria?->nombre;

                if ((string) $article->seguimiento === 'cantidad') {
                    $available = $this->consumableQuantity(
                        $personnel,
                        $category,
                        (string) $article->nombre,
                        (int) $unit->id,
                        (int) $article->id,
                    );
                    $minimum = max(1, (int) ceil($available * 0.2));

                    $consumableRows[] = [
                        'unidad_id' => $unit->id,
                        'articulo_id' => $article->id,
                        'cantidad_disponible' => $available,
                        'cantidad_asignada' => 0,
                        'cantidad_mantenimiento' => 0,
                        'stock_minimo' => $minimum,
                        'created_at' => $now,
                        'updated_at' => $now,
                    ];
                    $unitConsumables += $available;

                    continue;
                }

                $quantity = $this->serializedQuantity(
                    $personnel,
                    $category,
                    (string) $article->nombre,
                    (int) $unit->id,
                    (int) $article->id,
                );

                for ($sequence = 1; $sequence <= $quantity; $sequence++) {
                    [$state, $condition] = $this->seriesStatus(
                        (int) $unit->id,
                        (int) $article->id,
                        $sequence,
                    );

                    $seriesRows[] = [
                        'articulo_id' => $article->id,
                        'unidad_id' => $unit->id,
                        'codigo_serie' => sprintf(
                            'TEST-%s-A%03d-%05d',
                            $unitCode,
                            $article->id,
                            $sequence,
                        ),
                        'observaciones' => self::GENERATED_MARKER,
                        'estado' => $state,
                        'condicion_actual' => $condition,
                        'operacion_detalle_id_actual' => null,
                        'created_at' => $now,
                        'updated_at' => $now,
                        'deleted_at' => null,
                    ];
                }

                $unitSeries += $quantity;
            }

            $summary[] = [
                'unidad' => $unit->nombre,
                'personal' => $personnel,
                'consumibles' => $unitConsumables,
                'series' => $unitSeries,
            ];
        }

        return [$consumableRows, $seriesRows, $summary];
    }

    private function consumableQuantity(
        int $personnel,
        string $category,
        string $name,
        int $unitId,
        int $articleId,
    ): int {
        $factor = match ($category) {
            'Municion' => str_contains($name, '9 mm') ? 12.0 : 7.0,
            'Agentes quimicos' => str_contains($name, 'Proyectil') ? 0.8 : 0.45,
            'Primeros auxilios' => str_contains($name, 'Guantes') ? 1.5 : 0.55,
            default => 0.5,
        };

        return max(5, (int) ceil($personnel * $factor * $this->variation($unitId, $articleId)));
    }

    private function serializedQuantity(
        int $personnel,
        string $category,
        string $name,
        int $unitId,
        int $articleId,
    ): int {
        $divisor = match ($category) {
            'Proteccion' => str_contains($name, 'balistico') || str_contains($name, 'antibalas')
                ? 35
                : 22,
            'Accesorios tacticos' => 18,
            'Armamento menos letal' => match (true) {
                str_contains($name, 'Megafono') => 180,
                str_contains($name, 'Escopeta') => 100,
                str_contains($name, 'Rifle') || str_contains($name, 'Pistola') => 120,
                default => 75,
            },
            'Vehiculos tacticos' => match (true) {
                str_contains($name, 'Neptuno') => 1500,
                str_contains($name, 'VTA') => 1200,
                default => 800,
            },
            default => 50,
        };

        $base = (int) ceil($personnel / $divisor);
        $varied = (int) round($base * $this->variation($unitId, $articleId));

        return max(1, $varied);
    }

    private function variation(int $unitId, int $articleId): float
    {
        $bucket = crc32("{$unitId}:{$articleId}") % 31;

        return 0.85 + ($bucket / 100);
    }

    private function seriesStatus(int $unitId, int $articleId, int $sequence): array
    {
        $bucket = crc32("{$unitId}:{$articleId}:{$sequence}") % 100;

        return match (true) {
            $bucket < 82 => ['disponible', 'bueno'],
            $bucket < 91 => ['disponible', 'con_defectos'],
            $bucket < 96 => ['observado', 'malo'],
            default => ['inoperativo', 'inoperativo'],
        };
    }

    private function unitCode(Unidad $unit): string
    {
        $source = $unit->codigo_externo ?: $unit->sigla ?: (string) $unit->id;
        $code = strtoupper(preg_replace('/[^A-Za-z0-9]+/', '', $source) ?: (string) $unit->id);

        return substr($code, 0, 16);
    }
}
