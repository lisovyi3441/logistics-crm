<?php

declare(strict_types=1);

namespace App\Logistics\Cargo;

use Illuminate\Pipeline\Pipeline;
use App\Logistics\Cargo\Pipes\CalculateBasePrice;
use App\Logistics\Cargo\Pipes\ApplyInsuranceFee;
use App\Logistics\Cargo\Pipes\ApplyDangerousGoodsSurcharge;
use App\Logistics\Cargo\Pipes\ApplyVolumeDiscount;
use App\Logistics\Cargo\Pipes\ApplyTax;

class PricingPipeline
{
    public function calculate(PricingData $data): PricingData
    {
        return app(Pipeline::class)
            ->send($data)
            ->through([
                CalculateBasePrice::class,
                ApplyInsuranceFee::class,
                ApplyDangerousGoodsSurcharge::class,
                ApplyVolumeDiscount::class,
                ApplyTax::class,
            ])
            ->then(fn (PricingData $processedData) => $processedData);
    }
}
