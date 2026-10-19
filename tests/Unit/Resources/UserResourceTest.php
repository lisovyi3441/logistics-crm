<?php

declare(strict_types=1);

namespace Tests\Unit\Resources;

use App\Http\Resources\UserResource;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class UserResourceTest extends TestCase
{
    use RefreshDatabase;

    public function test_it_returns_correct_user_data(): void
    {
        $user = User::factory()->create([
            'name' => 'John Doe',
            'email' => 'john@example.com',
        ]);

        $resource = new UserResource($user);
        $data = $resource->resolve();

        $this->assertEquals('John Doe', $data['name']);
        $this->assertEquals('john@example.com', $data['email']);
    }

    public function test_it_transforms_loaded_company_and_roles(): void
    {
        $this->seed(\Database\Seeders\RoleSeeder::class);
        $company = \App\Models\Company::factory()->create(['name' => 'Acme Corp']);
        $user = User::factory()->create(['company_id' => $company->id]);
        $user->assignRole('admin');

        $user->load(['company', 'roles']);

        $resource = new UserResource($user);
        $data = $resource->resolve();

        $this->assertArrayHasKey('company', $data);
        $this->assertEquals('Acme Corp', $data['company']['name']);
        $this->assertArrayHasKey('roles', $data);
        $this->assertTrue($data['roles']->contains('admin'));
    }
}
