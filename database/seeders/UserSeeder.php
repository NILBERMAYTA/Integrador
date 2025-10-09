<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use App\Models\User;

class UserSeeder extends Seeder
{
    public function run(): void
    {
        User::updateOrCreate(
            ['email' => 'admin@armutop.local'],
            [
                'name' => 'Admin ARMUTOP',
                'password' => Hash::make('admin123'),
                'role' => 'admin',
                'can_login' => true,
            ]
        );

        User::updateOrCreate(
            ['email' => 'furriel@armutop.local'],
            [
                'name' => 'Furriel UTOP',
                'password' => Hash::make('furriel123'),
                'role' => 'furriel',
                'can_login' => true,
            ]
        );

        // Policía sin acceso (sin email)
        User::firstOrCreate(
            ['email' => null, 'numero_escalafon' => 'P-001'],
            [
                'name' => 'Policía Demo',
                'password' => null,
                'role' => 'policia',
                'can_login' => false,
                'rango' => 'Sgto.',
                'fecha_ingreso' => now()->subYears(3)->toDateString(),
            ]
        );
    }
}
