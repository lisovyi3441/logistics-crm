<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Truck extends Model
{
    /** @use HasFactory<\Database\Factories\TruckFactory> */
    use HasFactory;

    protected $fillable = [
        'name',
        'max_weight_kg',
        'max_volume_cbm',
    ];
}
