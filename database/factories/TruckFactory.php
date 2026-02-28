<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Truck>
 */
class TruckFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'name' => $this->faker->randomElement(['Van (Sprinter)', 'Lorry (7.5t)', 'Standard Trailer (13.6m)', 'Mega Trailer', 'Refrigerated Trailer']),
            'max_weight_kg' => $this->faker->randomFloat(2, 1000, 24000),
            'max_volume_cbm' => $this->faker->randomFloat(2, 12, 120),
        ];
    }
}
