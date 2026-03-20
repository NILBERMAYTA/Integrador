<?php

namespace Database\Factories;

use App\Models\Articulo;
use App\Models\ArticuloSerie;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Inspeccion>
 */
class InspeccionFactory extends Factory
{
    public function definition(): array
    {
        $serie = ArticuloSerie::query()->inRandomOrder()->first();
        $articulo = $serie?->articulo ?? Articulo::query()->inRandomOrder()->first() ?? Articulo::factory()->create();

        return [
            'articulo_id' => $articulo->id,
            'serie_id' => $serie?->id,
            'inspector_id' => User::query()->whereIn('role', ['administrador_general', 'administrador_unidad', 'furriel'])->inRandomOrder()->value('id')
                ?? User::factory()->furriel()->create()->id,
            'resultado' => fake()->randomElement(['apto', 'observado', 'inoperativo']),
            'observaciones' => fake()->optional()->sentence(),
            'realizada_en' => fake()->dateTimeBetween('-30 days', 'now'),
        ];
    }
}
