<?php

namespace Database\Factories;

use App\Models\Categoria;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Articulo>
 */
class ArticuloFactory extends Factory
{
    public function definition(): array
    {
        $tipo = fake()->randomElement(['reutilizable', 'consumible']);
        return [
            'categoria_id' => Categoria::query()->inRandomOrder()->value('id') ?? Categoria::factory(),
            'nombre' => ucfirst(fake()->unique()->words(2, true)),
            'unidad_medida' => null,
            'descripcion' => null,
            'foto_path' => null,
            'tipo' => $tipo,
            'seguimiento' => $tipo === 'reutilizable' ? 'serie' : 'cantidad',
        ];
    }

    public function serie(): static
    {
        return $this->state(fn () => [
            'tipo' => 'reutilizable',
            'seguimiento' => 'serie',
            'unidad_medida' => null,
        ]);
    }

    public function cantidad(): static
    {
        return $this->state(fn () => [
            'tipo' => 'consumible',
            'seguimiento' => 'cantidad',
        ]);
    }
}
