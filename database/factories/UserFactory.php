<?php

namespace Database\Factories;

use App\Models\Unidad;
use App\Models\User;
use App\Services\UserTransferService;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Facades\Hash;

class UserFactory extends Factory
{
    protected $model = User::class;

    public function configure()
    {
        return $this->afterCreating(function (User $user) {
            if (! empty($user->role)) {
                $user->syncRoles([$user->role]);
            }

            if (! $user->asignacionesUnidad()->exists() && $user->unidad_id) {
                app(UserTransferService::class)->registerInitialAssignment($user, $user, $user->unidad_id, 'Asignacion via factory');
            }
        });
    }

    public function definition(): array
    {
        $rangos = ['Sgto.', 'Sgto. My.', 'Tte.', 'Cap.', 'My.', 'Tcnl.', 'Cnl.'];
        $role = $this->faker->randomElement(['policia', 'furriel', 'administrador_unidad']);
        $unidadId = Unidad::query()->inRandomOrder()->value('id') ?? Unidad::factory()->create()->id;

        return [
            'name' => $this->faker->firstName(),
            'apellido_paterno' => $this->faker->lastName(),
            'apellido_materno' => $this->faker->lastName(),
            'email' => $this->faker->unique()->safeEmail(),
            'password' => Hash::make('password123'),
            'role' => $role,
            'can_login' => in_array($role, ['administrador_general', 'administrador_unidad', 'furriel'], true),
            'rango' => $this->faker->randomElement($rangos),
            'numero_escalafon' => 'P-'.str_pad($this->faker->unique()->numberBetween(1, 9999), 4, '0', STR_PAD_LEFT),
            'fecha_ingreso' => $this->faker->dateTimeBetween('-10 years', '-1 year')->format('Y-m-d'),
            'unidad_id' => $unidadId,
        ];
    }

    public function administradorGeneral(): static
    {
        return $this->state(fn () => [
            'role' => 'administrador_general',
            'can_login' => true,
        ]);
    }

    public function administradorUnidad(): static
    {
        return $this->state(fn () => [
            'role' => 'administrador_unidad',
            'can_login' => true,
        ]);
    }

    public function furriel(): static
    {
        return $this->state(fn () => [
            'role' => 'furriel',
            'can_login' => true,
        ]);
    }

    public function policia(): static
    {
        return $this->state(fn () => [
            'role' => 'policia',
            'can_login' => false,
            'email' => null,
            'password' => null,
        ]);
    }
}
