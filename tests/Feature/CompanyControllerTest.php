<?php

use App\Models\Company;
use App\Models\User;
use Spatie\Permission\Models\Role;

use function Pest\Laravel\actingAs;

beforeEach(function () {
    $this->seed(\Database\Seeders\RoleSeeder::class);

    $this->admin = User::factory()->create();
    $this->admin->assignRole('admin');

    $this->manager = User::factory()->create();
    $this->manager->assignRole('manager');
});

it('allows admin to view companies index', function () {
    actingAs($this->admin)
        ->get('/companies')
        ->assertOk();
});

it('prevents manager from viewing companies index', function () {
    actingAs($this->manager)
        ->get('/companies')
        ->assertForbidden();
});

it('allows admin to create a valid company', function () {
    actingAs($this->admin)
        ->post('/companies', [
            'name' => 'Test Company',
            'vat_number' => 'PL123456789',
            'address' => 'Kyiv, Ukraine',
            'contact_phone' => '+380671234567',
            'contact_email' => 'test@gmail.com',
        ])
        ->assertRedirect(route('companies.index'));

    $this->assertDatabaseHas('companies', [
        'name' => 'Test Company',
        'vat_number' => 'PL123456789',
    ]);
});

it('prevents creating company with invalid vat formatting', function () {
    actingAs($this->admin)
        ->post('/companies', [
            'name' => 'Test Company',
            'vat_number' => 'invalid vat format',
        ])
        ->assertSessionHasErrors(['vat_number']);
});

it('prevents deleting a company with users', function () {
    $company = Company::factory()->create();
    $user = User::factory()->create(['company_id' => $company->id]);

    actingAs($this->admin)
        ->delete("/companies/{$company->id}")
        ->assertSessionHasErrors(['message']);

    $this->assertDatabaseHas('companies', ['id' => $company->id]);
});

it('allows deleting a company without related records', function () {
    $company = Company::factory()->create();

    actingAs($this->admin)
        ->delete("/companies/{$company->id}")
        ->assertRedirect(route('companies.index'));

    $this->assertDatabaseMissing('companies', ['id' => $company->id]);
});

it('allows manager to edit their own company', function () {
    $company = Company::factory()->create();
    $this->manager->update(['company_id' => $company->id]);

    actingAs($this->manager)
        ->put("/companies/{$company->id}", [
            'name' => 'My Updated Company',
            'address' => 'Kyiv',
        ])
        ->assertRedirect(route('companies.index'));

    $this->assertDatabaseHas('companies', [
        'id' => $company->id,
        'name' => 'My Updated Company',
    ]);
});

it('forbids manager from editing another company', function () {
    $myCompany = Company::factory()->create();
    $this->manager->update(['company_id' => $myCompany->id]);

    $otherCompany = Company::factory()->create(['name' => 'Other Company']);

    actingAs($this->manager)
        ->put("/companies/{$otherCompany->id}", [
            'name' => 'Hacked Company',
            'address' => 'Kyiv',
        ])
        ->assertForbidden();

    $this->assertDatabaseHas('companies', [
        'id' => $otherCompany->id,
        'name' => 'Other Company',
    ]);
});
