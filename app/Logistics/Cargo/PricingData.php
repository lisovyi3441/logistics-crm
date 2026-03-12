<?php

declare(strict_types=1);

namespace App\Logistics\Cargo;

/**
 * Data transfer object for pricing pipeline.
 */
class PricingData
{
    /**
     * Create a new pricing data object.
     *
     * @param  float  $weightKg  Cargo weight in kg.
     * @param  float  $cbm  Cargo volume in m3.
     * @param  bool  $isDangerous  Whether the cargo is dangerous (ADR).
     * @param  float  $distanceKm  Route distance in km.
     * @param  float  $declaredValueCents  Declared value in cents for insurance.
     */
    /**
     * @param  array<string, mixed>  $requestData
     */
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
        public private(set) float $finalPriceCents = 0,
        public array $requestData = []
    ) {}

    /**
     * Calculate the final price based on all components.
     */
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
