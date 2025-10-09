<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\ArticuloSerie>
 */
class ArticuloSerieFactory extends Factory
{
    public function definition(): array
    {
        return [
            'articulo_id' => 1,
            'codigo_serie' => strtoupper(fake()->bothify('SER-###??')),
            'observaciones' => null,
        ];
    }
}
