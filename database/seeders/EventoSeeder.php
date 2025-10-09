<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Evento;

class EventoSeeder extends Seeder
{
    public function run(): void
    {
        Evento::updateOrCreate(
            ['nombre' => 'Operativo Piloto'],
            [
                'descripcion' => 'Simulación de desbloqueo',
                'fecha_inicio' => now()->subDay(),
                'fecha_fin' => null,
            ]
        );
    }
}
