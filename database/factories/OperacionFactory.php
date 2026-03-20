<?php

namespace Database\Factories;

use App\Models\Evento;
use App\Models\Unidad;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Operacion>
 */
class OperacionFactory extends Factory
{
    public function definition(): array
    {
        $unidadId = Unidad::query()->inRandomOrder()->value('id')
            ?? Unidad::factory()->create()->id;

        $actorId = User::query()->whereIn('role', ['administrador_unidad', 'furriel', 'administrador_general'])->inRandomOrder()->value('id')
            ?? User::factory()->furriel()->create()->id;

        return [
            'tipo' => fake()->randomElement(['asignacion', 'devolucion', 'consumo', 'mantenimiento_salida', 'mantenimiento_retorno', 'ajuste']),
            'evento_id' => fake()->boolean(70) ? (Evento::query()->inRandomOrder()->value('id') ?? Evento::factory()->create()->id) : null,
            'usuario_destino_id' => User::query()->where('role', 'policia')->inRandomOrder()->value('id'),
            'actor_id' => $actorId,
            'unidad_id' => $unidadId,
            'fecha' => fake()->dateTimeBetween('-3 months', 'now'),
            'observaciones' => fake()->sentence(),
            'operacion_padre_id' => null,
        ];
    }
}
