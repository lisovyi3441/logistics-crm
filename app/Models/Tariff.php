<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Tariff extends Model
{
    protected $fillable = [
        'price_per_km_cents',
        'insurance_rate_percent',
        'tax_rate_percent',
        'adr_surcharge_percent',
    ];
}
