<?php

namespace Database\Factories;

use App\Models\Articulo;
use App\Models\Unidad;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\ArticuloSerie>
 */
class ArticuloSerieFactory extends Factory
{
    public function definition(): array
    {
        $articuloId = Articulo::query()->where('tipo', 'reutilizable')->inRandomOrder()->value('id')
            ?? Articulo::factory()->serie()->create()->id;

        $unidadId = Unidad::query()->inRandomOrder()->value('id')
            ?? Unidad::factory()->create()->id;

        return [
            'articulo_id' => $articuloId,
            'unidad_id' => $unidadId,
            'codigo_serie' => strtoupper(fake()->bothify('SER-###??')),
            'observaciones' => null,
            'estado' => 'disponible',
            'operacion_detalle_id_actual' => null,
        ];
    }
}
