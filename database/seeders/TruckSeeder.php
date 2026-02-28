<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class TruckSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $trucks = [
            ['name' => 'Van (Sprinter)', 'max_weight_kg' => 1200, 'max_volume_cbm' => 14],
            ['name' => 'Lorry (7.5t)', 'max_weight_kg' => 3000, 'max_volume_cbm' => 35],
            ['name' => 'Standard Trailer (13.6m)', 'max_weight_kg' => 24000, 'max_volume_cbm' => 90],
            ['name' => 'Mega Trailer', 'max_weight_kg' => 24000, 'max_volume_cbm' => 100],
            ['name' => 'Refrigerated Trailer', 'max_weight_kg' => 22000, 'max_volume_cbm' => 88],
        ];

        foreach ($trucks as $truck) {
            \App\Models\Truck::create($truck);
        }
    }
}
