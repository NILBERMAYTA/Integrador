<?php

namespace Database\Seeders;

use App\Models\Evento;
use Illuminate\Database\Seeder;

class EventoSeeder extends Seeder
{
    public function run(): void
    {
        Evento::updateOrCreate(
            ['nombre' => 'Operativo de control urbano'],
            [
                'descripcion' => 'Despliegue preventivo y control de orden publico en area urbana',
                'fecha_inicio' => now()->subDay(),
                'fecha_fin' => null,
            ]
        );
    }
}
