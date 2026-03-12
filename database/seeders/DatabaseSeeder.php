<?php

declare(strict_types=1);

namespace Database\Seeders;

use App\Models\Company;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\User;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        $this->call([
            RoleSeeder::class,
            TruckSeeder::class,
        ]);

        $myCompany = Company::factory()->create([
            'name' => 'Modern Logistics Ltd',
        ]);

        $clientCompany = Company::factory()->create([
            'name' => 'Agro-Transit LLC',
        ]);

        $adminUser = User::factory()->create([
            'name' => 'Administrator',
            'email' => 'admin@gmail.com',
            'password' => bcrypt('password'),
            'company_id' => $myCompany->id,
        ]);
        $adminUser->assignRole('admin');

        $managerUser = User::factory()->create([
            'name' => 'Demo Manager',
            'email' => 'manager@gmail.com',
            'password' => bcrypt('password'),
            'company_id' => $clientCompany->id,
        ]);
        $managerUser->assignRole('manager');

        $observerUser = User::factory()->create([
            'name' => 'Demo Observer',
            'email' => 'observer@gmail.com',
            'password' => bcrypt('password'),
            'company_id' => $clientCompany->id,
        ]);
        $observerUser->assignRole('observer');

        $demoOrders = Order::factory(15)->create([
            'company_id' => $clientCompany->id,
            'user_id' => $managerUser->id,
        ]);

        $cargoItems = ['Wheat', 'Corn', 'Sunflower', 'Nitrogen fertilizers', 'Pallets with electronics', 'Agricultural machinery parts', 'Medicines', 'Building materials', 'Furniture', 'Clothing and textiles'];

        foreach ($demoOrders as $order) {
            for ($i = 0; $i < rand(1, 4); $i++) {
                OrderItem::create([
                    'order_id' => $order->id,
                    'name' => collect($cargoItems)->random(),
                    'quantity' => rand(1, 10),
                    'weight_kg' => rand(10, 500) / 10,
                    'declared_value_cents' => rand(1000, 50000),
                ]);
            }
        }

        $companies = Company::factory(10)->create();

        foreach ($companies as $company) {
            // One manager per company
            $manager = User::factory()
                ->state(['company_id' => $company->id])
                ->manager()
                ->create();

            // Four observers
            $observers = User::factory(4)
                ->state(['company_id' => $company->id])
                ->observer()
                ->create();

            $users = collect([$manager])->concat($observers);

            $orders = Order::factory(20)
                ->create([
                    'company_id' => $company->id,
                    'user_id' => $manager->id,
                ]);

            foreach ($orders as $order) {
                for ($i = 0; $i < rand(1, 4); $i++) {
                    OrderItem::create([
                        'order_id' => $order->id,
                        'name' => collect($cargoItems)->random(),
                        'quantity' => rand(1, 10),
                        'weight_kg' => rand(10, 500) / 10,
                        'declared_value_cents' => rand(1000, 50000),
                    ]);
                }
            }
        }
    }
}
