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
            ['nombre' => 'Armamento menos letal', 'descripcion' => 'Bastones, rifles, escopetas y dispositivos empleados en control de multitudes'],
            ['nombre' => 'Agentes quimicos', 'descripcion' => 'Granadas, proyectiles, sprays y balones lacrimogenos o fumigenos'],
            ['nombre' => 'Municion', 'descripcion' => 'Cartuchos, proyectiles y otros insumos de municion'],
            ['nombre' => 'Vehiculos tacticos', 'descripcion' => 'Vehiculos antidisturbios, lanza agua y apoyo tactico'],
            ['nombre' => 'Primeros auxilios', 'descripcion' => 'Insumos basicos de atencion prehospitalaria para operaciones UTOP'],
            ['nombre' => 'Accesorios tacticos', 'descripcion' => 'Elementos de porte, iluminacion, sujecion y apoyo operativo'],
        ];

        foreach ($data as $row) {
            Categoria::updateOrCreate(['nombre' => $row['nombre']], $row);
        }
    }
}
