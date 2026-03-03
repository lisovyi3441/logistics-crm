<?php

use App\Models\Tariff;
use App\Models\User;

use function Pest\Laravel\actingAs;

beforeEach(function () {
    $this->seed(\Database\Seeders\RoleSeeder::class);

    $this->admin = User::factory()->create();
    $this->admin->assignRole('admin');

    $this->manager = User::factory()->create();
    $this->manager->assignRole('manager');

    $this->tariff = Tariff::firstOrCreate([
        'tax_rate_percent' => 20,
        'insurance_rate_percent' => 1,
        'price_per_km_cents' => 100,
        'adr_surcharge_percent' => 25,
    ]);
});

it('allows admin to edit global tariffs', function () {
    actingAs($this->admin)
        ->get(route('tariffs.edit'))
        ->assertOk()
        ->assertInertia(fn ($page) => $page
            ->has('tariff')
        );
});

it('forbids manager from editing tariffs', function () {
    actingAs($this->manager)
        ->get(route('tariffs.edit'))
        ->assertForbidden();
});

it('allows admin to update global tariffs', function () {
    actingAs($this->admin)
        ->from(route('tariffs.edit'))
        ->put(route('tariffs.update'), [
            'tax_rate_percent' => 15, // Change to 15%
            'insurance_rate_percent' => 2.5,
            'adr_surcharge_percent' => 30,
        ])
        ->assertRedirect(route('tariffs.edit'));

    $this->assertDatabaseHas('tariffs', [
        'tax_rate_percent' => 15,
        'adr_surcharge_percent' => 30,
    ]);
});

it('forbids manager from updating tariffs', function () {
    actingAs($this->manager)
        ->from(route('tariffs.edit'))
        ->put(route('tariffs.update'), [
            'tax_rate_percent' => 15,
            'insurance_rate_percent' => 2.5,
            'adr_surcharge_percent' => 30,
        ])
        ->assertForbidden();

    // Ensure database hasn't changed
    $this->assertDatabaseHas('tariffs', [
        'tax_rate_percent' => 20,
    ]);
});
