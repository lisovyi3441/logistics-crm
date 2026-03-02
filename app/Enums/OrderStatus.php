<?php

declare(strict_types=1);

namespace App\Enums;

enum OrderStatus: string
{
    case NEW = 'new';
    case PENDING = 'pending';
    case IN_TRANSIT = 'in transit';
    case DELIVERED = 'delivered';
    case CANCELED = 'canceled';

    public function label(): string
    {
        return match ($this) {
            self::NEW => 'New Order',
            self::PENDING => 'Processing',
            self::IN_TRANSIT => 'On the road',
            self::DELIVERED => 'Completed',
            self::CANCELED => 'Cancelled',
        };
    }

    public function color(): string
    {
        return match ($this) {
            self::NEW => 'bg-blue-100 text-blue-800',
            self::PENDING => 'bg-yellow-100 text-yellow-800',
            self::IN_TRANSIT => 'bg-purple-100 text-purple-800',
            self::DELIVERED => 'bg-green-100 text-green-800',
            self::CANCELED => 'bg-red-100 text-red-800',
        };
    }

    /**
     * Define the state machine. Which statuses can this status transition TO?
     */
    public function allowedTransitions(): array
    {
        return match ($this) {
            self::NEW => [self::PENDING, self::CANCELED],
            self::PENDING => [self::IN_TRANSIT, self::CANCELED],
            self::IN_TRANSIT => [self::DELIVERED],
            self::DELIVERED, self::CANCELED => [],
        };
    }

    public function canTransitionTo(self $newStatus): bool
    {
        return in_array($newStatus, $this->allowedTransitions(), true);
    }
}
