<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\VehicleType>
 */
class VehicleTypeFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'name' => $this->faker->word.' Van',
            'max_weight_kg' => $this->faker->numberBetween(1000, 24000),
            'max_volume_cbm' => $this->faker->numberBetween(5, 120),
            'base_price_per_km_cents' => $this->faker->numberBetween(50, 300),
        ];
    }
}
