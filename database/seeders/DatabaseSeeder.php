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
            'name' => 'ТОВ "Сучасна Логістика"',
        ]);

        $clientCompany = Company::factory()->create([
            'name' => 'ТОВ "Агро-Транзит"',
        ]);

        $adminUser = User::factory()->create([
            'name' => 'Адміністратор',
            'email' => 'admin@gmail.com',
            'password' => bcrypt('password'),
            'company_id' => $myCompany->id,
        ]);
        $adminUser->assignRole('admin');

        $managerUser = User::factory()->create([
            'name' => 'Менеджер Демо',
            'email' => 'manager@gmail.com',
            'password' => bcrypt('password'),
            'company_id' => $clientCompany->id,
        ]);
        $managerUser->assignRole('manager');

        $observerUser = User::factory()->create([
            'name' => 'Спостерігач Демо',
            'email' => 'observer@gmail.com',
            'password' => bcrypt('password'),
            'company_id' => $clientCompany->id,
        ]);
        $observerUser->assignRole('observer');

        $demoOrders = Order::factory(15)->create([
            'company_id' => $clientCompany->id,
            'user_id' => $managerUser->id,
        ]);

        $cargoItems = ['Пшениця озима', 'Кукурудза', 'Соняшник', 'Добрива азотні', 'Палети з електронікою', 'Запчастини для с/г техніки', 'Медикаменти', 'Будівельні матеріали', 'Меблі', 'Одяг та текстиль'];

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
            $users = User::factory(5)->create([
                'company_id' => $company->id,
            ]);

            $orders = Order::factory(20)
                ->recycle($users)
                ->create([
                    'company_id' => $company->id,
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
