<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Models\Company;
use App\Models\User;
use Database\Seeders\RoleSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class CompanyPolicyTest extends TestCase
{
    use RefreshDatabase;

    private User $admin;

    private User $companyUser;

    private User $otherCompanyUser;

    private Company $company;

    private Company $otherCompany;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(RoleSeeder::class);

        $this->company = Company::factory()->create();
        $this->otherCompany = Company::factory()->create();

        $this->admin = User::factory()->create();
        $this->admin->assignRole('admin');

        $this->companyUser = User::factory()->create(['company_id' => $this->company->id]);
        $this->otherCompanyUser = User::factory()->create(['company_id' => $this->otherCompany->id]);
    }

    public function test_view_any_companies(): void
    {
        $this->assertTrue($this->admin->can('viewAny', Company::class));
        $this->assertFalse($this->companyUser->can('viewAny', Company::class));
    }

    public function test_view_company(): void
    {
        $this->assertTrue($this->admin->can('view', $this->company));
        $this->assertTrue($this->companyUser->can('view', $this->company));
        $this->assertFalse($this->otherCompanyUser->can('view', $this->company));
    }

    public function test_create_company(): void
    {
        $this->assertTrue($this->admin->can('create', Company::class));
        $this->assertFalse($this->companyUser->can('create', Company::class));
    }

    public function test_update_company(): void
    {
        $this->assertTrue($this->admin->can('update', $this->company));
        $this->assertTrue($this->companyUser->can('update', $this->company));
        $this->assertFalse($this->otherCompanyUser->can('update', $this->company));
    }

    public function test_delete_company(): void
    {
        $this->assertTrue($this->admin->can('delete', $this->company));
        $this->assertFalse($this->companyUser->can('delete', $this->company));
    }
}
