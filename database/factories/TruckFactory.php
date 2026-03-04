<?php

namespace Database\Factories;

use App\Models\VehicleType;
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
            'name' => $this->faker->company.' Truck',
            'license_plate' => strtoupper($this->faker->lexify('??')).' '.$this->faker->numerify('####').' '.strtoupper($this->faker->lexify('??')),
            'vehicle_type_id' => VehicleType::factory(),
        ];
    }
}
