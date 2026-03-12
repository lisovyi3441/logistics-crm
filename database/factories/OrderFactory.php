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
        $cities = [
            ['address' => 'Kyiv, Khreshchatyk st., 1', 'lat' => 50.4501, 'lng' => 30.5234],
            ['address' => 'Lviv, Rynok sq., 1', 'lat' => 49.8397, 'lng' => 24.0297],
            ['address' => 'Odesa, Derybasivska st., 1', 'lat' => 46.4825, 'lng' => 30.7233],
            ['address' => 'Dnipro, Dmytra Yavornytskoho ave., 1', 'lat' => 48.4647, 'lng' => 35.0462],
            ['address' => 'Kharkiv, Svobody sq., 1', 'lat' => 49.9935, 'lng' => 36.2304],
            ['address' => 'Vinnytsia, Soborna st., 1', 'lat' => 49.2331, 'lng' => 28.4682],
            ['address' => 'Ivano-Frankivsk, Nezalezhnosti st., 1', 'lat' => 48.9226, 'lng' => 24.7111],
        ];

        $origin = fake()->randomElement($cities);
        $dest = fake()->randomElement(array_filter($cities, fn ($c) => $c['address'] !== $origin['address']));

        return [
            'user_id' => User::factory(),
            'company_id' => Company::factory(),
            'order_number' => fake()->unique()->bothify('ORD-####-????'),
            'status' => fake()->randomElement(OrderStatus::cases()),
            'currency' => 'UAH',
            'notes' => fake()->sentence(),

            // Geospatial Data (Realistic Ukrainian hubs)
            'pickup_address' => $origin['address'],
            'pickup_lat' => $origin['lat'],
            'pickup_lng' => $origin['lng'],
            'delivery_address' => $dest['address'],
            'delivery_lat' => $dest['lat'],
            'delivery_lng' => $dest['lng'],
            'distance_km' => fake()->randomFloat(2, 50, 800),
            'transit_time_minutes' => fake()->numberBetween(120, 1440), // 2 hours to 24 hours

            // Detailed Pricing Breakdown (so receipts don't break on seeded data)
            'total_price_cents' => fake()->numberBetween(50000, 500000),
            'base_price_cents' => fake()->numberBetween(40000, 400000),
            'insurance_fee_cents' => fake()->numberBetween(1000, 5000),
            'surcharge_cents' => 0,
            'discount_cents' => 0,
            'tax_cents' => fake()->numberBetween(5000, 50000),
        ];
    }
}
