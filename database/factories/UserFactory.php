<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Facades\Hash;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\User>
 */
class UserFactory extends Factory
{
    public function definition(): array
    {
        // Rangos posibles dentro de la policía
        $rangos = ['Sgto.', 'Sgto. My.', 'Tte.', 'Cap.', 'My.', 'Tcnl.', 'Cnl.'];

        // Roles posibles
        $roles = ['policia', 'furriel', 'admin'];

        // Determinar rol aleatorio (por defecto policia)
        $role = $this->faker->randomElement($roles);

        return [
            'name' => $this->faker->firstName(),
            'apellido_paterno' => $this->faker->lastName(),
            'apellido_materno' => $this->faker->lastName(),
            'email' => $this->faker->unique()->safeEmail(),
            'password' => Hash::make('password123'),
            'role' => $role,
            'can_login' => in_array($role, ['admin', 'furriel']), // solo ellos pueden ingresar
            'rango' => $this->faker->randomElement($rangos),
            'numero_escalafon' => 'P-' . str_pad($this->faker->unique()->numberBetween(1, 9999), 4, '0', STR_PAD_LEFT),
            'fecha_ingreso' => $this->faker->dateTimeBetween('-10 years', '-1 year')->format('Y-m-d'),
        ];
    }

    /**
     * Estado específico para administradores
     */
    public function admin(): static
    {
        return $this->state(fn() => [
            'role' => 'admin',
            'can_login' => true,
        ]);
    }

    /**
     * Estado específico para furrieles
     */
    public function furriel(): static
    {
        return $this->state(fn() => [
            'role' => 'furriel',
            'can_login' => true,
        ]);
    }

    /**
     * Estado específico para policías sin acceso
     */
    public function policia(): static
    {
        return $this->state(fn() => [
            'role' => 'policia',
            'can_login' => false,
            'email' => null,
            'password' => null,
        ]);
    }
}
