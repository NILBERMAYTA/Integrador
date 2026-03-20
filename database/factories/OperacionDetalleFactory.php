<?php

namespace Database\Factories;

use App\Models\Articulo;
use App\Models\Operacion;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\OperacionDetalle>
 */
class OperacionDetalleFactory extends Factory
{
    public function definition(): array
    {
        $articulo = Articulo::query()->inRandomOrder()->first() ?? Articulo::factory()->create();

        return [
            'operacion_id' => Operacion::query()->inRandomOrder()->value('id') ?? Operacion::factory(),
            'articulo_id' => $articulo->id,
            'cantidad' => $articulo->seguimiento === 'cantidad'
                ? fake()->randomFloat(2, 1, 25)
                : 0,
            'condicion' => fake()->randomElement(['nuevo', 'bueno', 'regular', 'danado', 'inoperativo']),
            'observaciones' => fake()->optional()->sentence(),
        ];
    }
}
