<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use App\Models\User;

class UserSeeder extends Seeder
{
    public function run(): void
    {
        //  Admin principal
        $admin = User::factory()->admin()->create([
            'name' => 'Admin',
            'apellido_paterno' => 'General',
            'apellido_materno' => 'UTOP',
            'email' => 'admin@armutop.local',
            'password' => Hash::make('admin123'),
        ]);
        $admin->syncRoles(['admin']);

        // Furrieles
        User::factory()->furriel()->count(5)->create()->each(function ($user) {
            $user->syncRoles(['furriel']);
        });

        // Policías sin acceso
        User::factory()->policia()->count(93)->create()->each(function ($user) {
            $user->syncRoles(['policia']);
        });

        // Total aproximado: 99 usuarios (1 admin + 5 furrieles + 93 policías)
    }
}
