<?php

namespace Database\Factories;

use App\Enums\OrderStatus;
use App\Enums\OrderType;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * Factory untuk membuat instance ProductionOrder
 * yang digunakan dalam testing dan seeding
 *
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\ProductionOrder>
 */
class ProductionOrderFactory extends Factory
{
    /**
     * Mendefinisikan state default untuk production order
     * dengan data realistis untuk testing
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $totalSheets = $this->faker->numberBetween(1000, 50000);
        $totalRims = (int) floor($totalSheets / 1000);
        $inschietSheets = $totalSheets % 1000;

        return [
            'po_number' => $this->faker->unique()->numberBetween(100000, 999999),
            'obc_number' => $this->faker->optional(0.7)->numerify('OBC-#####'),
            'order_type' => OrderType::Regular,
            'product_type' => $this->faker->randomElement(['pita cukai', 'hologram', 'label keamanan']),
            'total_sheets' => $totalSheets,
            'total_rims' => $totalRims,
            'start_rim' => 1,
            'end_rim' => $totalRims,
            'inschiet_sheets' => $inschietSheets,
            'team_id' => null,
            'status' => OrderStatus::Registered,
        ];
    }

    /**
     * State untuk order dengan tipe MMEA
     */
    public function mmea(): static
    {
        return $this->state(function (array $attributes) {
            $totalSheets = $this->faker->numberBetween(1000, 50000);
            $totalRims = (int) floor($totalSheets / 1000);

            return [
                'order_type' => OrderType::Mmea,
                'total_sheets' => $totalSheets,
                'total_rims' => $totalRims,
                'end_rim' => $totalRims,
                'inschiet_sheets' => 0, // MMEA tidak memiliki inschiet
            ];
        });
    }

    /**
     * State untuk order dengan tipe regular
     */
    public function regular(): static
    {
        return $this->state(fn (array $attributes) => [
            'order_type' => OrderType::Regular,
        ]);
    }

    /**
     * State untuk order yang sedang dalam proses
     */
    public function inProgress(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => OrderStatus::InProgress,
        ]);
    }

    /**
     * State untuk order yang sudah selesai
     */
    public function completed(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => OrderStatus::Completed,
        ]);
    }
}
