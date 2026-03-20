<?php

namespace Database\Seeders;

use App\Models\Unidad;
use App\Models\User;
use App\Services\UserTransferService;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class UserSeeder extends Seeder
{
    public function run(): void
    {
        $unidades = Unidad::query()->orderBy('id')->get();
        $service = app(UserTransferService::class);

        $admin = User::query()->updateOrCreate(
            ['email' => 'admin@armutop.local'],
            User::factory()->administradorGeneral()->raw([
                'name' => 'Admin',
                'apellido_paterno' => 'General',
                'apellido_materno' => 'UTOP',
                'email' => 'admin@armutop.local',
                'unidad_id' => $unidades->first()?->id,
            ])
        );
        $admin->password = Hash::make('admin123');
        $admin->save();
        $admin->syncRoles(['administrador_general']);
        $this->registrarHistorialInicial($service, $admin, $admin, $unidades->first()?->id);

        foreach ($unidades as $unidad) {
            $adminUnidad = User::query()->updateOrCreate(
                ['email' => strtolower($unidad->sigla).'@armutop.local'],
                User::factory()->administradorUnidad()->raw([
                    'name' => 'Administrador '.$unidad->sigla,
                    'apellido_paterno' => 'Unidad',
                    'apellido_materno' => $unidad->sigla,
                    'email' => strtolower($unidad->sigla).'@armutop.local',
                    'can_login' => true,
                    'unidad_id' => $unidad->id,
                ])
            );
            $adminUnidad->password = Hash::make('adminunidad123');
            $adminUnidad->save();
            $adminUnidad->syncRoles(['administrador_unidad']);
            $this->registrarHistorialInicial($service, $admin, $adminUnidad, $adminUnidad->unidad_id);

            $furriel = User::query()->updateOrCreate(
                ['email' => 'furriel.'.strtolower($unidad->sigla).'@armutop.local'],
                User::factory()->furriel()->raw([
                    'name' => 'Furriel '.$unidad->sigla,
                    'apellido_paterno' => 'Operativo',
                    'apellido_materno' => $unidad->sigla,
                    'email' => 'furriel.'.strtolower($unidad->sigla).'@armutop.local',
                    'can_login' => true,
                    'unidad_id' => $unidad->id,
                ])
            );
            $furriel->password = Hash::make('furriel123');
            $furriel->save();
            $furriel->syncRoles(['furriel']);
            $this->registrarHistorialInicial($service, $admin, $furriel, $furriel->unidad_id);

            for ($i = 1; $i <= 8; $i++) {
                $numeroEscalafon = sprintf('%s-P-%03d', $unidad->sigla, $i);

                $policia = User::query()->updateOrCreate(
                    ['numero_escalafon' => $numeroEscalafon],
                    User::factory()->policia()->raw([
                        'name' => 'Policia '.$i,
                        'apellido_paterno' => 'Demo',
                        'apellido_materno' => $unidad->sigla,
                        'numero_escalafon' => $numeroEscalafon,
                        'rango' => fake()->randomElement(['Sgto.', 'Tte.', 'Cap.']),
                        'unidad_id' => $unidad->id,
                    ])
                );

                $policia->syncRoles(['policia']);
                $this->registrarHistorialInicial($service, $admin, $policia, $policia->unidad_id);
            }
        }
    }

    private function registrarHistorialInicial(UserTransferService $service, User $actor, User $target, ?int $unidadId): void
    {
        if (! $unidadId) {
            return;
        }

        if (! $target->asignacionesUnidad()->exists()) {
            $service->registerInitialAssignment($actor, $target, $unidadId, 'Carga inicial de datos de prueba');
        }
    }
}
