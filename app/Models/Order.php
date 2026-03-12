<?php

declare(strict_types=1);

namespace App\Models;

use App\Enums\OrderStatus;
use App\Enums\Permissions;
use App\Observers\OrderObserver;
use Illuminate\Database\Eloquent\Attributes\ObservedBy;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * @property \App\Enums\OrderStatus $status
 * @property int $id
 * @property string $order_number
 * @property int $total_price_cents
 * @property int $base_price_cents
 * @property int $insurance_fee_cents
 * @property int $surcharge_cents
 * @property int $discount_cents
 * @property int $tax_cents
 * @property string $currency
 * @property string|null $pickup_address
 * @property string|null $delivery_address
 * @property float|null $distance_km
 * @property int|null $transit_time_minutes
 * @property \Illuminate\Support\Carbon $created_at
 */
#[ObservedBy(OrderObserver::class)]
class Order extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'company_id',
        'order_number',
        'status',
        'base_price_cents',
        'insurance_fee_cents',
        'surcharge_cents',
        'discount_cents',
        'tax_cents',
        'total_price_cents',
        'currency',
        'notes',
        'pickup_address',
        'pickup_lat',
        'pickup_lng',
        'delivery_address',
        'delivery_lat',
        'delivery_lng',
        'distance_km',
        'transit_time_minutes',
        'truck_id',
        'vehicle_type_id',
    ];

    protected function casts(): array
    {
        return [
            'status' => OrderStatus::class,
            'total_price_cents' => 'integer',
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function company(): BelongsTo
    {
        return $this->belongsTo(Company::class);
    }

    public function truck(): BelongsTo
    {
        return $this->belongsTo(Truck::class);
    }

    public function vehicleType(): BelongsTo
    {
        return $this->belongsTo(VehicleType::class);
    }

    public function items(): HasMany
    {
        return $this->hasMany(OrderItem::class);
    }

    public function statusHistories(): HasMany
    {
        return $this->hasMany(OrderStatusHistory::class);
    }

    public function documents(): HasMany
    {
        return $this->hasMany(OrderDocument::class);
    }

    /**
     * Scope a query to only include orders visible to the given user.
     */
    public function scopeForUser($query, User $user)
    {
        if ($user->can(Permissions::VIEW_ALL_ORDERS->value)) {
            return $query;
        }

        return $query->where('company_id', $user->company_id);
    }

    /**
     * Get the route key for the model.
     */
    public function getRouteKeyName(): string
    {
        return 'order_number';
    }
}
