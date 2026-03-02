<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Models\Company;
use Illuminate\Database\Eloquent\Factories\Factory;

class CompanyFactory extends Factory
{
    protected $model = Company::class;

    public function definition(): array
    {
        $companies = [
            'ТОВ "Агро-Транзит"', 'ПП "Захід Логістика"', 'ТОВ "МегаТранс"',
            'ПАТ "Південний Експрес"', 'ФОП Коваленко І.В.', 'ТОВ "Схід Карго"',
            'ПП "Київ-Трейд"', 'ТОВ "Дніпро-Вантаж"', 'ТОВ "Глобал Лоджистікс Укр"',
            'ПрАТ "Одеса-Порт-Транс"',
        ];

        return [
            'name' => fake()->randomElement($companies).' '.fake()->companySuffix(),
            'vat_number' => fake()->bothify('UA########'),
            'address' => fake()->address(),
            'contact_phone' => '+380'.fake()->numberBetween(660000000, 999999999),
            'contact_email' => fake()->unique()->safeEmail(),
        ];
    }
}
