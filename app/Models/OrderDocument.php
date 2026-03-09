<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class OrderDocument extends Model
{
    protected $fillable = [
        'order_id',
        'document_type',
        'path',
    ];

    public function order(): BelongsTo
    {
        return $this->belongsTo(Order::class);
    }
}
