<?php

use App\Enums\Roles;
use App\Jobs\GenerateDocumentJob;
use App\Models\Company;
use App\Models\Order;
use App\Models\User;
use Illuminate\Support\Facades\Queue;

beforeEach(function () {
    Queue::fake();
    $this->seed(\Database\Seeders\RoleSeeder::class);
    $this->admin = User::factory()->create()->assignRole('admin');
    
    $this->company1 = Company::factory()->create();
    $this->manager1 = User::factory()->create(['company_id' => $this->company1->id])->assignRole('manager');
    
    $this->company2 = Company::factory()->create();
    $this->manager2 = User::factory()->create(['company_id' => $this->company2->id])->assignRole('manager');
    
    $this->order = Order::factory()->create(['company_id' => $this->company1->id]);
});

test('admin can generate cmr and invoice', function () {
    $this->actingAs($this->admin)->post(route('orders.documents.generate', ['order' => $this->order->order_number, 'type' => 'cmr']))
        ->assertRedirect()
        ->assertSessionHas('success');
    
    Queue::assertPushed(GenerateDocumentJob::class, fn ($job) => $job->type === 'cmr' && $job->order->id === $this->order->id);

    $this->actingAs($this->admin)->post(route('orders.documents.generate', ['order' => $this->order->order_number, 'type' => 'invoice']))
        ->assertRedirect()
        ->assertSessionHas('success');
        
    Queue::assertPushed(GenerateDocumentJob::class, fn ($job) => $job->type === 'invoice');
});

test('manager can generate invoice for their company but not cmr', function () {
    $this->actingAs($this->manager1)->post(route('orders.documents.generate', ['order' => $this->order->order_number, 'type' => 'cmr']))
        ->assertForbidden();

    $this->actingAs($this->manager1)->post(route('orders.documents.generate', ['order' => $this->order->order_number, 'type' => 'invoice']))
        ->assertRedirect();
        
    Queue::assertPushed(GenerateDocumentJob::class, fn ($job) => $job->type === 'invoice');
});

test('manager cannot generate documents for other companies', function () {
    $this->actingAs($this->manager2)->post(route('orders.documents.generate', ['order' => $this->order->order_number, 'type' => 'invoice']))
        ->assertForbidden();
});

test('invalid document type returns 404', function () {
    $this->actingAs($this->admin)->post(route('orders.documents.generate', ['order' => $this->order->order_number, 'type' => 'invalid']))
        ->assertNotFound();
});
