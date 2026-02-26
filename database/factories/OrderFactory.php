<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Enums\OrderStatus;
use App\Models\Company;
use App\Models\Order;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

class OrderFactory extends Factory
{
    protected $model = Order::class;

    public function definition(): array
    {
        return [
            'user_id' => User::factory(),
            'company_id' => Company::factory(),
            'order_number' => fake()->unique()->bothify('ORD-####-????'),
            'status' => fake()->randomElement(OrderStatus::cases()),
            'total_price_cents' => fake()->numberBetween(10000, 1000000),
            'currency' => fake()->randomElement(['USD', 'EUR', 'UAH']),
            'notes' => fake()->sentence(),
        ];
    }
}
