<?php

use App\Models\Truck;
use App\Models\User;
use App\Models\VehicleType;

use function Pest\Laravel\actingAs;

beforeEach(function () {
    $this->seed(\Database\Seeders\RoleSeeder::class);

    $this->admin = User::factory()->create();
    $this->admin->assignRole('admin');

    $this->manager = User::factory()->create();
    $this->manager->assignRole('manager');

    $this->vehicleType = VehicleType::factory()->create();
});

it('allows admin to view truck index', function () {
    Truck::factory()->count(3)->create(['vehicle_type_id' => $this->vehicleType->id]);

    actingAs($this->admin)
        ->get(route('trucks.index'))
        ->assertOk()
        ->assertInertia(fn ($page) => $page
            ->has('trucks.data', 3)
        );
});

it('forbids manager from viewing truck index', function () {
    actingAs($this->manager)
        ->get(route('trucks.index'))
        ->assertForbidden();
});

it('allows admin to create a truck', function () {
    $data = [
        'name' => 'Volvo FH16',
        'license_plate' => 'AA 1234 BB',
        'vehicle_type_id' => $this->vehicleType->id,
    ];

    actingAs($this->admin)
        ->post(route('trucks.store'), $data)
        ->assertRedirect(route('trucks.index'));

    $this->assertDatabaseHas('trucks', $data);
});

it('forbids manager from creating a truck', function () {
    $data = [
        'name' => 'Volvo FH16',
        'license_plate' => 'AA 1234 BB',
        'vehicle_type_id' => $this->vehicleType->id,
    ];

    actingAs($this->manager)
        ->post(route('trucks.store'), $data)
        ->assertForbidden();
});

it('allows admin to update a truck', function () {
    $truck = Truck::factory()->create(['vehicle_type_id' => $this->vehicleType->id]);

    actingAs($this->admin)
        ->put(route('trucks.update', $truck), [
            'name' => 'Updated Volvo',
            'license_plate' => 'CC 9999 KA',
            'vehicle_type_id' => $this->vehicleType->id,
        ])
        ->assertRedirect(route('trucks.index'));

    $this->assertDatabaseHas('trucks', [
        'id' => $truck->id,
        'name' => 'Updated Volvo',
    ]);
});

it('allows admin to delete a free truck', function () {
    $truck = Truck::factory()->create(['vehicle_type_id' => $this->vehicleType->id]);

    actingAs($this->admin)
        ->delete(route('trucks.destroy', $truck))
        ->assertRedirect(route('trucks.index'));

    $this->assertDatabaseMissing('trucks', ['id' => $truck->id]);
});
