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
            'users.transfer',
            'units.manage',
            'categorias.manage',
            'articulos.manage',
            'eventos.manage',
            'mantenimientos.manage',
            'predicciones.view',
            'predicciones.train',
            'reposicion.view',
            'prestamos.view',
            'prestamos.manage',
            'activity_logs.view',
        ];

        foreach ($permissions as $permission) {
            Permission::findOrCreate($permission);
        }

        $adminGeneral = Role::findOrCreate('administrador_general');
        $adminUnidad = Role::findOrCreate('administrador_unidad');
        $furriel = Role::findOrCreate('furriel');
        $policia = Role::findOrCreate('policia');

        $adminGeneral->syncPermissions($permissions);
        $adminUnidad->syncPermissions([
            'dashboard.view',
            'users.manage',
            'categorias.manage',
            'articulos.manage',
            'eventos.manage',
            'mantenimientos.manage',
            'predicciones.view',
            'predicciones.train',
            'reposicion.view',
            'prestamos.view',
            'prestamos.manage',
            'activity_logs.view',
        ]);
        $furriel->syncPermissions([
            'dashboard.view',
            'articulos.manage',
            'mantenimientos.manage',
            'predicciones.view',
            'reposicion.view',
            'prestamos.view',
            'prestamos.manage',
        ]);
        $policia->syncPermissions([
            'prestamos.view',
            'reposicion.view',
        ]);

        $validRoles = ['administrador_general', 'administrador_unidad', 'furriel', 'policia'];

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
