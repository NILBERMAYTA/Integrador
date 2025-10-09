<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Categoria;

class CategoriaSeeder extends Seeder
{
    public function run(): void
    {
        $data = [
            ['nombre'=>'Protección','descripcion'=>'Chaleco, casco, rodilleras'],
            ['nombre'=>'Disuasivos','descripcion'=>'Escudos, granadas de humo'],
            ['nombre'=>'Munición','descripcion'=>'Cartuchos y proyectiles'],
        ];

        foreach ($data as $row) {
            Categoria::updateOrCreate(['nombre'=>$row['nombre']], $row);
        }
    }
}
