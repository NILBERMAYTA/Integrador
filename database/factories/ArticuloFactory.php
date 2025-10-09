<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Articulo>
 */
class ArticuloFactory extends Factory
{
    public function definition(): array
    {
        $tipo = fake()->randomElement(['reutilizable','consumible']);
        $seg  = $tipo==='reutilizable' ? 'serie' : 'cantidad';

        return [
            'categoria_id' => 1, // cámbialo/relaciónalo en el seeder con ->for()
            'nombre' => ucfirst(fake()->unique()->words(2, true)),
            'unidad_medida' => $seg==='serie' ? 'unidad' : fake()->randomElement(['unidad','caja','cartucho']),
            'descripcion' => null,
            'tipo' => $tipo,
            'seguimiento' => $seg,
        ];
    }
}
