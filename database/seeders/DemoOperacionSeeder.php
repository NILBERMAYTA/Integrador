<?php

namespace Database\Seeders;

use App\Models\Articulo;
use App\Models\ArticuloSerie;
use App\Models\Evento;
use App\Models\Operacion;
use App\Models\OperacionDetalle;
use App\Models\OperacionDetalleSerie;
use App\Models\Unidad;
use App\Models\User;
use App\Services\InventarioUnidadService;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class DemoOperacionSeeder extends Seeder
{
    public function run(): void
    {
        if (Operacion::query()->exists()) {
            return;
        }

        $evento = Evento::query()->first();
        $articulos = Articulo::query()->get()->keyBy('nombre');
        $inventario = app(InventarioUnidadService::class);

        foreach (Unidad::query()->get() as $unidad) {
            $actor = User::query()
                ->whereIn('role', ['administrador_unidad', 'furriel'])
                ->where('unidad_id', $unidad->id)
                ->orderBy('id')
                ->first();

            $policia = User::query()
                ->where('role', 'policia')
                ->where('unidad_id', $unidad->id)
                ->orderBy('id')
                ->first();

            if (! $actor || ! $policia) {
                continue;
            }

            DB::transaction(function () use ($unidad, $actor, $policia, $evento, $articulos, $inventario) {
                $ajuste = Operacion::create([
                    'tipo' => 'ajuste',
                    'evento_id' => null,
                    'usuario_destino_id' => null,
                    'actor_id' => $actor->id,
                    'unidad_id' => $unidad->id,
                    'fecha' => now()->subDays(5),
                    'observaciones' => 'Carga inicial de inventario demo',
                ]);

                foreach ([
                    'Municion 9 mm' => 180,
                    'Municion calibre 12' => 90,
                ] as $nombre => $cantidad) {
                    if (! isset($articulos[$nombre])) {
                        continue;
                    }

                    OperacionDetalle::create([
                        'operacion_id' => $ajuste->id,
                        'articulo_id' => $articulos[$nombre]->id,
                        'cantidad' => $cantidad,
                        'condicion' => 'nuevo',
                    ]);

                    $inventario->addInitialStock($unidad->id, $articulos[$nombre]->id, $cantidad);
                }

                $seriePrestamo = ArticuloSerie::query()
                    ->where('unidad_id', $unidad->id)
                    ->where('estado', 'disponible')
                    ->whereNull('deleted_at')
                    ->orderBy('id')
                    ->first();

                if (! $seriePrestamo || ! isset($articulos['Municion 9 mm'])) {
                    return;
                }

                $prestamo = Operacion::create([
                    'tipo' => 'asignacion',
                    'evento_id' => $evento?->id,
                    'usuario_destino_id' => $policia->id,
                    'actor_id' => $actor->id,
                    'unidad_id' => $unidad->id,
                    'fecha' => now()->subDays(2),
                    'observaciones' => 'Prestamo demo para pruebas',
                ]);

                OperacionDetalle::create([
                    'operacion_id' => $prestamo->id,
                    'articulo_id' => $articulos['Municion 9 mm']->id,
                    'cantidad' => 24,
                    'condicion' => 'bueno',
                ]);

                $inventario->assign($unidad->id, $articulos['Municion 9 mm'], 24);

                $detalleSerie = OperacionDetalle::create([
                    'operacion_id' => $prestamo->id,
                    'articulo_id' => $seriePrestamo->articulo_id,
                    'cantidad' => 1,
                    'condicion' => 'bueno',
                ]);

                OperacionDetalleSerie::create([
                    'operacion_detalle_id' => $detalleSerie->id,
                    'serie_id' => $seriePrestamo->id,
                ]);

                $seriePrestamo->update([
                    'estado' => 'asignado',
                    'operacion_detalle_id_actual' => $detalleSerie->id,
                ]);
            });
        }
    }
}
