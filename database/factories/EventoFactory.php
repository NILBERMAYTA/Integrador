<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Evento>
 */
class EventoFactory extends Factory
{
    public function definition(): array
    {
        return [
            'nombre' => 'Operativo '.fake()->city(),
            'descripcion' => fake()->sentence(),
            'fecha_inicio' => now()->subHours(fake()->numberBetween(1,72)),
            'fecha_fin' => null,
        ];
    }
}
