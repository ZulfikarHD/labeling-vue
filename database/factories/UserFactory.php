<?php

namespace Database\Factories;

use App\Enums\UserRole;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

/**
 * Factory untuk membuat instance User
 * yang digunakan dalam testing dan seeding
 *
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\User>
 */
class UserFactory extends Factory
{
    /**
     * Password yang digunakan secara default oleh factory
     */
    protected static ?string $password;

    /**
     * Mendefinisikan state default untuk user
     * dengan NP unik 5 digit
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'np' => $this->faker->unique()->numerify('#####'),
            'name' => fake()->name(),
            'password' => static::$password ??= Hash::make('password'),
            'role' => UserRole::Operator,
            'workstation_id' => null,
            'is_active' => true,
            'remember_token' => Str::random(10),
        ];
    }

    /**
     * State untuk user dengan role admin
     */
    public function admin(): static
    {
        return $this->state(fn (array $attributes) => [
            'role' => UserRole::Admin,
        ]);
    }

    /**
     * State untuk user dengan role operator
     */
    public function operator(): static
    {
        return $this->state(fn (array $attributes) => [
            'role' => UserRole::Operator,
        ]);
    }

    /**
     * State untuk user yang tidak aktif
     */
    public function inactive(): static
    {
        return $this->state(fn (array $attributes) => [
            'is_active' => false,
        ]);
    }
}
