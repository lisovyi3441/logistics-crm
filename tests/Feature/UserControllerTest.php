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

it('renders create form with companies and roles', function () {
    actingAs($this->admin)
        ->get('/users/create')
        ->assertSuccessful()
        ->assertInertia(fn ($page) => $page->component('users/Form'));
});

it('supports redirect_to query param on store', function () {
    $company = Company::factory()->create();

    actingAs($this->admin)
        ->post('/users', [
            'name' => 'Redirected User',
            'email' => 'redirected@gmail.com',
            'password' => 'SecurePass123',
            'password_confirmation' => 'SecurePass123',
            'role' => 'manager',
            'company_id' => $company->id,
            'redirect_to' => '/companies',
        ])
        ->assertRedirect('/companies');
});

it('redirects show route to edit route', function () {
    $user = User::factory()->create();

    actingAs($this->admin)
        ->get("/users/{$user->id}")
        ->assertRedirect(route('users.edit', $user));
});

it('renders edit form for another user', function () {
    $user = User::factory()->create();

    actingAs($this->admin)
        ->get("/users/{$user->id}/edit")
        ->assertSuccessful()
        ->assertInertia(fn ($page) => $page->component('users/Form'));
});

it('prevents updating self in user controller', function () {
    actingAs($this->admin)
        ->put("/users/{$this->admin->id}", [
            'name' => 'Self Name',
            'email' => 'self@gmail.com',
            'role' => 'admin',
        ])
        ->assertRedirect(route('users.index'))
        ->assertSessionHasErrors(['message']);
});

it('allows admin to update user with optional new password and redirect_to', function () {
    $company = Company::factory()->create();
    $user = User::factory()->create(['company_id' => $company->id]);
    $user->assignRole('manager');

    actingAs($this->admin)
        ->put("/users/{$user->id}", [
            'name' => 'Updated Name',
            'email' => 'updated@gmail.com',
            'password' => 'NewPassword456',
            'password_confirmation' => 'NewPassword456',
            'role' => 'observer',
            'company_id' => $company->id,
            'redirect_to' => '/companies',
        ])
        ->assertRedirect('/companies');

    $user->refresh();
    expect($user->name)->toBe('Updated Name')
        ->and($user->email)->toBe('updated@gmail.com')
        ->and(\Illuminate\Support\Facades\Hash::check('NewPassword456', $user->password))->toBeTrue()
        ->and($user->hasRole('observer'))->toBeTrue();

    // Update without password and without redirect_to
    actingAs($this->admin)
        ->put("/users/{$user->id}", [
            'name' => 'Final Name',
            'email' => 'final@gmail.com',
            'role' => 'manager',
            'company_id' => $company->id,
        ])
        ->assertRedirect(route('users.index'));

    expect($user->fresh()->name)->toBe('Final Name');
});

it('allows deleting an eligible user without orders', function () {
    $user = User::factory()->create();

    actingAs($this->admin)
        ->delete("/users/{$user->id}")
        ->assertRedirect()
        ->assertSessionHas('success');

    $this->assertDatabaseMissing('users', ['id' => $user->id]);
});
