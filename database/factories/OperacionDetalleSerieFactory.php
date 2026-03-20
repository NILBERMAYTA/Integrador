<?php

namespace Database\Factories;

use App\Models\ArticuloSerie;
use App\Models\OperacionDetalle;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\OperacionDetalleSerie>
 */
class OperacionDetalleSerieFactory extends Factory
{
    public function definition(): array
    {
        return [
            'operacion_detalle_id' => OperacionDetalle::query()->inRandomOrder()->value('id') ?? OperacionDetalle::factory(),
            'serie_id' => function (array $attributes) {
                $detalle = OperacionDetalle::query()->find($attributes['operacion_detalle_id']);

                if (!$detalle) {
                    return ArticuloSerie::factory()->create()->id;
                }

                return ArticuloSerie::query()
                    ->where('articulo_id', $detalle->articulo_id)
                    ->inRandomOrder()
                    ->value('id')
                    ?? ArticuloSerie::factory()->create([
                        'articulo_id' => $detalle->articulo_id,
                        'unidad_id' => $detalle->operacion?->unidad_id,
                    ])->id;
            },
        ];
    }
}
