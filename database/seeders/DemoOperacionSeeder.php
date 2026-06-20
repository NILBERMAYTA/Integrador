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

                $stockInicial = [
                    'Municion 9 mm' => 180,
                    'Municion calibre 12' => 90,
                    'Cartucho impulsor calibre 12' => 40,
                    'Cartucho perdigon de goma calibre 12' => 80,
                    'Granada lacrimogena simple accion CS' => 48,
                    'Granada lacrimogena triple accion CS' => 36,
                    'Granada fumigena HC' => 24,
                    'Granada de aturdimiento' => 18,
                    'Proyectil lacrimogeno 37/38 mm corto alcance' => 72,
                    'Proyectil lacrimogeno 37/38 mm largo alcance' => 72,
                    'Spray gas pimienta OC 500 ml' => 24,
                    'Balon lanza gas CN/CS' => 8,
                    'Filtro para mascara antigas' => 60,
                    'Ferulas de inmovilizacion' => 12,
                    'Vendas elasticas' => 40,
                    'Paquetes de gasas' => 30,
                    'Algodon hidrofilo' => 16,
                    'Agua oxigenada' => 12,
                    'Alcohol medicinal' => 12,
                    'Guantes de exploracion' => 80,
                    'Guantes quirurgicos' => 80,
                    'Jeringa 5cc aguja 21' => 30,
                    'Cloruro de sodio 1L' => 18,
                ];

                $stockMinimo = [
                    'Municion 9 mm' => 60,
                    'Municion calibre 12' => 30,
                    'Cartucho impulsor calibre 12' => 12,
                    'Cartucho perdigon de goma calibre 12' => 24,
                    'Granada lacrimogena simple accion CS' => 12,
                    'Granada lacrimogena triple accion CS' => 10,
                    'Granada fumigena HC' => 8,
                    'Granada de aturdimiento' => 6,
                    'Proyectil lacrimogeno 37/38 mm corto alcance' => 24,
                    'Proyectil lacrimogeno 37/38 mm largo alcance' => 24,
                    'Spray gas pimienta OC 500 ml' => 8,
                    'Balon lanza gas CN/CS' => 2,
                    'Filtro para mascara antigas' => 20,
                    'Ferulas de inmovilizacion' => 4,
                    'Vendas elasticas' => 12,
                    'Paquetes de gasas' => 10,
                    'Algodon hidrofilo' => 4,
                    'Agua oxigenada' => 4,
                    'Alcohol medicinal' => 4,
                    'Guantes de exploracion' => 20,
                    'Guantes quirurgicos' => 20,
                    'Jeringa 5cc aguja 21' => 10,
                    'Cloruro de sodio 1L' => 6,
                ];

                foreach ($stockInicial as $nombre => $cantidad) {
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
                    $inventario->setMinimumStock($unidad->id, $articulos[$nombre]->id, $stockMinimo[$nombre] ?? 0);
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
