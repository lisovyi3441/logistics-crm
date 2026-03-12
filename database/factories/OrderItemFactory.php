<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Models\Order;
use App\Models\OrderItem;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\OrderItem>
 */
class OrderItemFactory extends Factory
{
    protected $model = OrderItem::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'order_id' => Order::factory(),
            'name' => $this->faker->words(3, true),
            'quantity' => $this->faker->numberBetween(1, 10),
            'weight_kg' => $this->faker->randomFloat(2, 0.5, 1000),
            'declared_value_cents' => $this->faker->numberBetween(1000, 100000),
            'length_cm' => $this->faker->numberBetween(10, 200),
            'width_cm' => $this->faker->numberBetween(10, 200),
            'height_cm' => $this->faker->numberBetween(10, 200),
            'cbm' => $this->faker->randomFloat(3, 0.1, 5),
            'is_dangerous' => $this->faker->boolean(10),
        ];
    }
}
