<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\User>
 */
class UserFactory extends Factory
{
    /**
     * The current password being used by the factory.
     */
    protected static ?string $password;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $role = fake()->randomElement(['admin','furriel','policia']);
        return [
            'name' => fake()->name(),
            'email' => $role==='policia' && fake()->boolean(30) ? null : fake()->unique()->safeEmail(),
            'email_verified_at' => now(),
            'password' => $role==='policia' && fake()->boolean(30) ? null : Hash::make('secret123'),
            'role' => $role,
            'can_login' => in_array($role, ['admin','furriel']),
            'rango' => fake()->randomElement(['Sgto.','Sbtte.','Tte.']),
            'numero_escalafon' => 'P-'.fake()->unique()->numberBetween(100,999),
            'fecha_ingreso' => fake()->date(),
            'remember_token' => Str::random(10),
        ];
    }

    /**
     * Indicate that the model's email address should be unverified.
     */
    public function unverified(): static
    {
        return $this->state(fn (array $attributes) => [
            'email_verified_at' => null,
        ]);
    }

    /**
     * Indicate that the model does not have two-factor authentication configured.
     */
    public function withoutTwoFactor(): static
    {
        return $this->state(fn (array $attributes) => [
            'two_factor_secret' => null,
            'two_factor_recovery_codes' => null,
            'two_factor_confirmed_at' => null,
        ]);
    }
}
