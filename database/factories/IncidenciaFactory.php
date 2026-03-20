<?php

namespace Database\Factories;

use App\Models\ArticuloSerie;
use App\Models\TipoIncidente;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Incidencia>
 */
class IncidenciaFactory extends Factory
{
    public function definition(): array
    {
        $serie = ArticuloSerie::query()->inRandomOrder()->first() ?? ArticuloSerie::factory()->create();

        return [
            'tipo_id' => TipoIncidente::query()->inRandomOrder()->value('id') ?? TipoIncidente::factory(),
            'articulo_id' => $serie->articulo_id,
            'serie_id' => $serie->id,
            'policia_id' => User::query()->where('role', 'policia')->inRandomOrder()->value('id')
                ?? User::factory()->policia()->create()->id,
            'descripcion' => fake()->sentence(),
            'fecha' => fake()->dateTimeBetween('-20 days', 'now'),
            'created_por' => User::query()->whereIn('role', ['administrador_general', 'administrador_unidad', 'furriel'])->inRandomOrder()->value('id')
                ?? User::factory()->furriel()->create()->id,
        ];
    }
}
