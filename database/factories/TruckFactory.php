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
            'license_plate' => $this->faker->unique()->regexify('[A-Z]{2}\s\d{4}\s[A-Z]{2}'),
        ];
    }
}
