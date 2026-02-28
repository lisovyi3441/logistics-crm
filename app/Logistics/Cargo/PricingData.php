<?php

declare(strict_types=1);

namespace App\Logistics\Cargo;

class PricingData
{
    public function __construct(
        public float $weightKg,
        public float $cbm,
        public bool $isDangerous,
        public float $declaredValueCents = 0,
        public float $insuranceFeeCents = 0,
        public float $basePriceCents = 0,
        public float $surchargeCents = 0,
        public float $discountCents = 0,
        public float $taxCents = 0,
        public float $finalPriceCents = 0
    ) {
    }

    public function calculateFinalPrice(): float
    {
        $this->finalPriceCents = $this->basePriceCents + $this->surchargeCents + $this->insuranceFeeCents - $this->discountCents + $this->taxCents;
        return $this->finalPriceCents;
    }
}
