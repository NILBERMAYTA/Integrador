<?php

namespace Database\Factories;

use App\Models\Articulo;
use App\Models\ArticuloSerie;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Mantenimiento>
 */
class MantenimientoFactory extends Factory
{
    public function definition(): array
    {
        $serie = ArticuloSerie::query()->inRandomOrder()->first();
        $articulo = $serie?->articulo ?? Articulo::query()->inRandomOrder()->first() ?? Articulo::factory()->create();

        return [
            'articulo_id' => $articulo->id,
            'serie_id' => $serie?->id,
            'created_por' => User::query()->whereIn('role', ['administrador_general', 'administrador_unidad', 'furriel'])->inRandomOrder()->value('id')
                ?? User::factory()->furriel()->create()->id,
            'tipo' => fake()->randomElement(['preventivo', 'correctivo']),
            'descripcion' => fake()->sentence(),
            'fecha_inicio' => fake()->dateTimeBetween('-30 days', '-2 days'),
            'fecha_fin' => fake()->optional()->dateTimeBetween('-1 day', 'now'),
            'costo' => fake()->optional()->randomFloat(2, 50, 500),
        ];
    }
}
