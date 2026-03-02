<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

class TruckSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // 1. Seed Global Tariffs
        \App\Models\Tariff::create([
            'price_per_km_cents' => 50,
            'insurance_rate_percent' => 1.00,
            'tax_rate_percent' => 20.00,
            'adr_surcharge_percent' => 25.00,
        ]);

        // 2. Seed Vehicle Types
        $types = [
            ['name' => 'Мікроавтобус (Бус)', 'max_weight_kg' => 1500, 'max_volume_cbm' => 12, 'base_price_per_km_cents' => 20],
            ['name' => '5-тонник (Тент)', 'max_weight_kg' => 5000, 'max_volume_cbm' => 36, 'base_price_per_km_cents' => 35],
            ['name' => '10-тонник (Ізотерм)', 'max_weight_kg' => 10000, 'max_volume_cbm' => 45, 'base_price_per_km_cents' => 50],
            ['name' => 'Фура (Тент 22т)', 'max_weight_kg' => 22000, 'max_volume_cbm' => 86, 'base_price_per_km_cents' => 70],
            ['name' => 'Фура (Рефрижератор 20т)', 'max_weight_kg' => 20000, 'max_volume_cbm' => 82, 'base_price_per_km_cents' => 85],
        ];

        $brandMap = [
            'Мікроавтобус (Бус)' => ['Mercedes-Benz Sprinter', 'Renault Master', 'Volkswagen Crafter'],
            '5-тонник (Тент)' => ['MAN TGL', 'IVECO Eurocargo', 'Mercedes-Benz Atego'],
            '10-тонник (Ізотерм)' => ['DAF LF', 'Volvo FL', 'Renault D-Wide'],
            'Фура (Тент 22т)' => ['DAF XF', 'Scania R450', 'Volvo FH16'],
            'Фура (Рефрижератор 20т)' => ['Volvo FH', 'Scania S500', 'Mercedes-Benz Actros'],
        ];

        foreach ($types as $typeData) {
            $type = \App\Models\VehicleType::create($typeData);

            // 3. Seed Physical Trucks (2 per type) with UA plates
            for ($i = 1; $i <= 2; $i++) {
                $region = collect(['AA', 'BC', 'KA', 'CE', 'BX', 'AT', 'BH'])->random();
                $letters = collect(['AA', 'AB', 'AC', 'AE', 'AH', 'AK', 'AM'])->random();
                $numbers = str_pad((string) rand(1000, 9999), 4, '0', STR_PAD_LEFT);
                $plate = "{$region} {$numbers} {$letters}";

                $brandName = collect($brandMap[$typeData['name']])->random();

                \App\Models\Truck::create([
                    'name' => "{$brandName}",
                    'license_plate' => $plate,
                    'vehicle_type_id' => $type->id,
                ]);
            }
        }
    }
}
