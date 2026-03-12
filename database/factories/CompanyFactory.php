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
            'Agro-Transit LLC', 'West Logistics Ltd', 'MegaTrans Corp',
            'South Express JSC', 'Kovalenko Freight', 'East Cargo LLC',
            'Kyiv-Trade PE', 'Dnipro-Freight LLC', 'Global Logistics Ukr Ltd',
            'Odesa-Port-Trans JSC',
        ];

        return [
            'name' => fake()->randomElement($companies).' '.fake()->companySuffix(),
            'vat_number' => fake()->bothify('UA########'),
            'address' => fake()->address(),
            'contact_phone' => '+380'.fake()->numberBetween(660000000, 999999999),
            'contact_email' => fake()->unique()->userName().'@gmail.com',
        ];
    }
}
