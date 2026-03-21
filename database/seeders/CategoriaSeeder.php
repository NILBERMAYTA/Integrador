<?php

namespace Database\Seeders;

use App\Models\Categoria;
use Illuminate\Database\Seeder;

class CategoriaSeeder extends Seeder
{
    public function run(): void
    {
        $data = [
            ['nombre' => 'Proteccion', 'descripcion' => 'Chalecos, cascos, escudos y otros elementos de proteccion personal'],
            ['nombre' => 'Municion', 'descripcion' => 'Cartuchos, proyectiles y otros insumos de municion'],
        ];

        foreach ($data as $row) {
            Categoria::updateOrCreate(['nombre' => $row['nombre']], $row);
        }

        Categoria::query()
            ->whereNotIn('nombre', collect($data)->pluck('nombre'))
            ->delete();
    }
}
