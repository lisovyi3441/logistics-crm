<?php

use App\Models\Company;
use App\Models\Order;
use App\Models\User;
use Spatie\Permission\Models\Role;

use function Pest\Laravel\actingAs;

beforeEach(function () {
    // Ensure roles exist
    Role::firstOrCreate(['name' => 'admin']);
    Role::firstOrCreate(['name' => 'manager']);
    Role::firstOrCreate(['name' => 'observer']);

    $this->admin = User::factory()->create();
    $this->admin->assignRole('admin');

    $this->manager = User::factory()->create();
    $this->manager->assignRole('manager');
});

it('allows admin to view users index', function () {
    actingAs($this->admin)
        ->get('/users')
        ->assertOk();
});

it('prevents manager from viewing users index', function () {
    actingAs($this->manager)
        ->get('/users')
        ->assertForbidden();
});

it('allows admin to create a valid user', function () {
    $company = Company::factory()->create();

    actingAs($this->admin)
        ->post('/users', [
            'name' => 'John Doe',
            'email' => 'johndoe@gmail.com',
            'password' => 'SecurePass123',
            'password_confirmation' => 'SecurePass123',
            'role' => 'manager',
            'company_id' => $company->id,
        ])
        ->assertRedirect(route('users.index'));

    $this->assertDatabaseHas('users', [
        'email' => 'johndoe@gmail.com',
        'company_id' => $company->id,
    ]);

    $user = User::where('email', 'johndoe@gmail.com')->first();
    expect($user->hasRole('manager'))->toBeTrue();
});

it('prevents admin from creating a manager without a company', function () {
    actingAs($this->admin)
        ->post('/users', [
            'name' => 'No Company Man',
            'email' => 'nocompany@gmail.com',
            'password' => 'SecurePass123',
            'password_confirmation' => 'SecurePass123',
            'role' => 'manager',
            'company_id' => null,
        ])
        ->assertSessionHasErrors(['company_id']);
});

it('prevents admin from editing their own profile in the CRUD', function () {
    actingAs($this->admin)
        ->get("/users/{$this->admin->id}/edit")
        ->assertRedirect(route('users.index'))
        ->assertSessionHasErrors(['message']);
});

it('prevents admin from deleting themselves', function () {
    actingAs($this->admin)
        ->delete("/users/{$this->admin->id}")
        ->assertSessionHasErrors(['message']);

    $this->assertDatabaseHas('users', ['id' => $this->admin->id]);
});

it('prevents deleting a user with associated orders', function () {
    $user = User::factory()->create();
    Order::factory()->create(['user_id' => $user->id]);

    actingAs($this->admin)
        ->delete("/users/{$user->id}")
        ->assertSessionHasErrors(['message']);

    $this->assertDatabaseHas('users', ['id' => $user->id]);
});
