<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use App\Models\User;

class UserSeeder extends Seeder
{
    public function run(): void
    {
        // 1️⃣ Admin principal
        User::factory()->admin()->create([
            'name' => 'Admin',
            'apellido_paterno' => 'General',
            'apellido_materno' => 'UTOP',
            'email' => 'admin@armutop.local',
            'password' => Hash::make('admin123'),
        ]);

        // 2️⃣ Furrieles
        User::factory()->furriel()->count(5)->create();

        // 3️⃣ Policías sin acceso
        User::factory()->policia()->count(93)->create();

        // Total aproximado: 99 usuarios (1 admin + 5 furrieles + 93 policías)
    }
}
