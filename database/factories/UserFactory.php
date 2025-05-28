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
        return [
            'name' => fake()->name(),
            'email' => fake()->unique()->safeEmail(),
            'password' => static::$password ??= Hash::make('password'), // 'password' como contraseña para todos los usuarios de factory
            'role' => 'cliente', // Rol por defecto en minúsculas
        ];
    }

    /**
     * Indicate that the model's email address should be unverified.
     */
    public function unverified(): static
    {
        return $this->state(fn(array $attributes) => [
            'email_verified_at' => null,
        ]);
    }

    /**
     * Indicate that the user is an administrator.
     */
    public function administrador(): static
    {
        return $this->state(fn(array $attributes) => [
            'role' => 'administrador',
        ]);
    }

    /**
     * Indicate that the user is a gerente.
     */
    public function gerente(): static
    {
        return $this->state(fn(array $attributes) => [
            'role' => 'gerente',
        ]);
    }
}
