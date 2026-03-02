<?php

declare(strict_types=1);

namespace App\Logistics\Cargo;

class PricingData
{
    public function __construct(
        public private(set) float $weightKg,
        public private(set) float $cbm,
        public private(set) bool $isDangerous,
        public private(set) float $distanceKm = 1,
        public private(set) float $declaredValueCents = 0,
        public private(set) float $insuranceFeeCents = 0,
        public private(set) float $basePriceCents = 0,
        public private(set) float $surchargeCents = 0,
        public private(set) float $discountCents = 0,
        public private(set) float $taxCents = 0,
        public private(set) float $finalPriceCents = 0
    ) {}

    public function calculateFinalPrice(): float
    {
        $this->finalPriceCents = $this->basePriceCents + $this->surchargeCents + $this->insuranceFeeCents - $this->discountCents + $this->taxCents;

        return $this->finalPriceCents;
    }

    public function setBasePriceCents(float $cents): void
    {
        $this->basePriceCents = $cents;
    }

    public function setInsuranceFeeCents(float $cents): void
    {
        $this->insuranceFeeCents = $cents;
    }

    public function setSurchargeCents(float $cents): void
    {
        $this->surchargeCents = $cents;
    }

    public function setDiscountCents(float $cents): void
    {
        $this->discountCents = $cents;
    }

    public function setTaxCents(float $cents): void
    {
        $this->taxCents = $cents;
    }
}
