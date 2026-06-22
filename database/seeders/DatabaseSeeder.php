<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        $this->call([
            ThemeSeeder::class,
            RolePermissionSeeder::class,
            CategoriaSeeder::class,
            TipoIncidenteSeeder::class,
            ArticuloSeeder::class,
            EventoSeeder::class,
            DemoOperacionSeeder::class,
            PrediccionDemoSeeder::class,
        ]);
    }
}
