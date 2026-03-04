<?php

use App\Models\User;
use Spatie\Permission\Models\Role;

use function Pest\Laravel\actingAs;

beforeEach(function () {
    Role::firstOrCreate(['name' => 'admin']);
    Role::firstOrCreate(['name' => 'manager']);
    Role::firstOrCreate(['name' => 'observer']);

    $this->admin = User::factory()->create();
    $this->admin->assignRole('admin');
});

it('allows admin to update user role without changing email', function () {
    $company = \App\Models\Company::factory()->create();
    $user = User::factory()->create([
        'email' => 'existing@google.com',
        'company_id' => $company->id,
    ]);
    $user->assignRole('observer');

    $response = actingAs($this->admin)
        ->put("/users/{$user->id}", [
            'name' => 'Valid Name',
            'email' => 'existing@google.com',
            'role' => 'manager',
            'company_id' => $company->id,
        ]);

    if ($response->exception) {
        dump($response->exception->getMessage());
    }

    $errors = session('errors');
    if ($errors) {
        dump($errors->getBag('default')->getMessages());
    }

    $response->assertSessionHasNoErrors();
    $response->assertRedirect();

    expect($user->fresh()->hasRole('manager'))->toBeTrue();
});
