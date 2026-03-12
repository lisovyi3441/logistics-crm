<?php

declare(strict_types=1);

namespace Tests\Unit\Resources;

use App\Http\Resources\CompanyResource;
use App\Models\Company;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class CompanyResourceTest extends TestCase
{
    use RefreshDatabase;

    public function test_it_returns_correct_data(): void
    {
        $company = Company::factory()->create([
            'name' => 'Test Corp',
            'vat_number' => 'UA12345678',
        ]);

        $resource = new CompanyResource($company);
        $data = $resource->toArray(request());

        $this->assertEquals($company->id, $data['id']);
        $this->assertEquals('Test Corp', $data['name']);
        $this->assertEquals('UA12345678', $data['vat_number']);
    }
}
