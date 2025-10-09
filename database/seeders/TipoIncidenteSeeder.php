<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\TipoIncidente;

class TipoIncidenteSeeder extends Seeder
{
    public function run(): void
    {
        $data = [
            ['nombre'=>'No devuelto','severidad'=>3],
            ['nombre'=>'Perdido','severidad'=>5],
            ['nombre'=>'Dañado','severidad'=>4],
        ];

        foreach ($data as $row) {
            TipoIncidente::updateOrCreate(['nombre'=>$row['nombre']], $row);
        }
    }
}
