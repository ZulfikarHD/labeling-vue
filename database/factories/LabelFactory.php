<?php

namespace Database\Factories;

use App\Enums\CutSide;
use App\Models\ProductionOrder;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * Factory untuk membuat instance Label
 * yang digunakan dalam testing dan seeding
 *
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Label>
 */
class LabelFactory extends Factory
{
    /**
     * Mendefinisikan state default untuk label
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'production_order_id' => ProductionOrder::factory(),
            'rim_number' => $this->faker->numberBetween(1, 100),
            'cut_side' => $this->faker->randomElement([CutSide::Left, CutSide::Right]),
            'is_inschiet' => false,
            'inspector_np' => null,
            'inspector_2_np' => null,
            'pack_sheets' => null,
            'started_at' => null,
            'finished_at' => null,
            'workstation_id' => null,
        ];
    }

    /**
     * State untuk label left side
     */
    public function left(): static
    {
        return $this->state(fn (array $attributes) => [
            'cut_side' => CutSide::Left,
        ]);
    }

    /**
     * State untuk label right side
     */
    public function right(): static
    {
        return $this->state(fn (array $attributes) => [
            'cut_side' => CutSide::Right,
        ]);
    }

    /**
     * State untuk label inschiet (rim 999)
     */
    public function inschiet(): static
    {
        return $this->state(fn (array $attributes) => [
            'rim_number' => 999,
            'is_inschiet' => true,
        ]);
    }

    /**
     * State untuk label MMEA (tanpa cut side)
     */
    public function mmea(): static
    {
        return $this->state(fn (array $attributes) => [
            'cut_side' => null,
        ]);
    }

    /**
     * State untuk label yang sedang dalam proses inspeksi
     */
    public function inProgress(): static
    {
        return $this->state(fn (array $attributes) => [
            'inspector_np' => $this->faker->numerify('#####'),
            'started_at' => now()->subMinutes($this->faker->numberBetween(1, 30)),
            'finished_at' => null,
        ]);
    }

    /**
     * State untuk label yang sudah selesai diinspeksi
     */
    public function completed(): static
    {
        return $this->state(fn (array $attributes) => [
            'inspector_np' => $this->faker->numerify('#####'),
            'started_at' => now()->subMinutes($this->faker->numberBetween(30, 60)),
            'finished_at' => now()->subMinutes($this->faker->numberBetween(1, 29)),
        ]);
    }
}
