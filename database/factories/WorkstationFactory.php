<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * Factory untuk membuat instance Workstation
 * yang digunakan dalam testing dan seeding
 *
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Workstation>
 */
class WorkstationFactory extends Factory
{
    /**
     * Mendefinisikan state default untuk workstation
     * dengan nama tim yang realistis
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'name' => 'Team '.$this->faker->unique()->numberBetween(1, 20),
            'is_active' => true,
        ];
    }

    /**
     * State untuk workstation yang tidak aktif
     */
    public function inactive(): static
    {
        return $this->state(fn (array $attributes) => [
            'is_active' => false,
        ]);
    }
}
