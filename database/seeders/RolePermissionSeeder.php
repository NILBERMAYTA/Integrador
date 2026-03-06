<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use Spatie\Permission\PermissionRegistrar;

class RolePermissionSeeder extends Seeder
{
    public function run(): void
    {
        app(PermissionRegistrar::class)->forgetCachedPermissions();

        $permissions = [
            'dashboard.view',
            'users.manage',
            'categorias.manage',
            'articulos.manage',
            'eventos.manage',
            'mantenimientos.manage',
            'prestamos.view',
            'prestamos.manage',
            'activity_logs.view',
        ];

        foreach ($permissions as $permission) {
            Permission::findOrCreate($permission);
        }

        $admin = Role::findOrCreate('admin');
        $furriel = Role::findOrCreate('furriel');
        $policia = Role::findOrCreate('policia');

        $admin->syncPermissions($permissions);
        $furriel->syncPermissions([
            'dashboard.view',
            'articulos.manage',
            'mantenimientos.manage',
            'prestamos.view',
            'prestamos.manage',
        ]);
        $policia->syncPermissions([
            'prestamos.view',
        ]);

        $validRoles = ['admin', 'furriel', 'policia'];

        User::query()
            ->whereNotNull('role')
            ->whereIn('role', $validRoles)
            ->chunkById(200, function ($users) {
                foreach ($users as $user) {
                    $user->syncRoles([$user->role]);
                }
            });
    }
}
