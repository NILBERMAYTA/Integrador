<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        $this->call([
            RolePermissionSeeder::class,
            UserSeeder::class,
            CategoriaSeeder::class,
            TipoIncidenteSeeder::class,
            ArticuloSeeder::class,
            EventoSeeder::class,
        ]);
    }
}
