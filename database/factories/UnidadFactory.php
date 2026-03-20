<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Unidad>
 */
class UnidadFactory extends Factory
{
    public function definition(): array
    {
        return [
            'nombre' => 'Unidad '.$this->faker->unique()->city(),
            'sigla' => strtoupper($this->faker->unique()->bothify('UTOP-??')),
            'descripcion' => $this->faker->sentence(),
        ];
    }
}
