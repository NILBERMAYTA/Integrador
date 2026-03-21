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
                'name' => 'Carlos',
                'apellido_paterno' => 'Mamani',
                'apellido_materno' => 'Quispe',
                'email' => 'admin@armutop.local',
                'unidad_id' => $unidades->first()?->id,
            ])
        );
        $admin->password = Hash::make('admin123');
        $admin->save();
        $admin->syncRoles(['administrador_general']);
        $this->registrarHistorialInicial($service, $admin, $admin, $unidades->first()?->id);

        $administradoresUnidad = [
            ['name' => 'Juan Pablo', 'apellido_paterno' => 'Choque', 'apellido_materno' => 'Apaza'],
            ['name' => 'Luis Fernando', 'apellido_paterno' => 'Flores', 'apellido_materno' => 'Condori'],
            ['name' => 'Marco Antonio', 'apellido_paterno' => 'Rojas', 'apellido_materno' => 'Huanca'],
        ];

        $furrieles = [
            ['name' => 'Rene', 'apellido_paterno' => 'Vilca', 'apellido_materno' => 'Ticona'],
            ['name' => 'Jorge', 'apellido_paterno' => 'Cutipa', 'apellido_materno' => 'Mendoza'],
            ['name' => 'Edwin', 'apellido_paterno' => 'Laura', 'apellido_materno' => 'Mamani'],
        ];

        $policias = [
            ['name' => 'Victor', 'apellido_paterno' => 'Quispe', 'apellido_materno' => 'Mamani'],
            ['name' => 'Jose Luis', 'apellido_paterno' => 'Condori', 'apellido_materno' => 'Apaza'],
            ['name' => 'Rodolfo', 'apellido_paterno' => 'Flores', 'apellido_materno' => 'Choque'],
            ['name' => 'Milton', 'apellido_paterno' => 'Lopez', 'apellido_materno' => 'Huanca'],
            ['name' => 'Hugo', 'apellido_paterno' => 'Ticona', 'apellido_materno' => 'Quisbert'],
            ['name' => 'Ruben', 'apellido_paterno' => 'Mendoza', 'apellido_materno' => 'Callisaya'],
            ['name' => 'Jhonny', 'apellido_paterno' => 'Villca', 'apellido_materno' => 'Mollo'],
            ['name' => 'Cristian', 'apellido_paterno' => 'Patzi', 'apellido_materno' => 'Condori'],
        ];

        foreach ($unidades as $index => $unidad) {
            $adminData = $administradoresUnidad[$index % count($administradoresUnidad)];
            $adminUnidad = User::query()->updateOrCreate(
                ['email' => strtolower($unidad->sigla).'@armutop.local'],
                User::factory()->administradorUnidad()->raw([
                    'name' => $adminData['name'],
                    'apellido_paterno' => $adminData['apellido_paterno'],
                    'apellido_materno' => $adminData['apellido_materno'],
                    'email' => strtolower($unidad->sigla).'@armutop.local',
                    'can_login' => true,
                    'unidad_id' => $unidad->id,
                ])
            );
            $adminUnidad->password = Hash::make('adminunidad123');
            $adminUnidad->save();
            $adminUnidad->syncRoles(['administrador_unidad']);
            $this->registrarHistorialInicial($service, $admin, $adminUnidad, $adminUnidad->unidad_id);

            $furrielData = $furrieles[$index % count($furrieles)];
            $furriel = User::query()->updateOrCreate(
                ['email' => 'furriel.'.strtolower($unidad->sigla).'@armutop.local'],
                User::factory()->furriel()->raw([
                    'name' => $furrielData['name'],
                    'apellido_paterno' => $furrielData['apellido_paterno'],
                    'apellido_materno' => $furrielData['apellido_materno'],
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
                $policiaData = $policias[($i - 1) % count($policias)];

                $policia = User::query()->updateOrCreate(
                    ['numero_escalafon' => $numeroEscalafon],
                    User::factory()->policia()->raw([
                        'name' => $policiaData['name'],
                        'apellido_paterno' => $policiaData['apellido_paterno'],
                        'apellido_materno' => $policiaData['apellido_materno'],
                        'numero_escalafon' => $numeroEscalafon,
                        'rango' => fake()->randomElement(['Sgto.', 'Sgto. My.', 'Tte.', 'Cap.']),
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
