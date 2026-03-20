<?php

namespace Database\Seeders;

use App\Models\Unidad;
use Illuminate\Database\Seeder;

class UnidadSeeder extends Seeder
{
    public function run(): void
    {
        foreach ([
            ['nombre' => 'UTOP El Alto', 'sigla' => 'UTOP-EA', 'descripcion' => 'Unidad base del sistema en El Alto'],
            ['nombre' => 'UTOP La Paz', 'sigla' => 'UTOP-LP', 'descripcion' => 'Unidad operativa de La Paz'],
            ['nombre' => 'UTOP Cochabamba', 'sigla' => 'UTOP-CB', 'descripcion' => 'Unidad operativa de Cochabamba'],
        ] as $unidad) {
            Unidad::updateOrCreate(['sigla' => $unidad['sigla']], $unidad);
        }
    }
}
