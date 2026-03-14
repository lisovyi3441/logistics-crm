<?php

use App\Jobs\GenerateDocumentJob;
use App\Models\Company;
use App\Models\Order;
use App\Models\OrderDocument;
use App\Models\User;
use Illuminate\Support\Facades\Bus;
use Illuminate\Support\Facades\Storage;

use function Pest\Laravel\actingAs;

beforeEach(function () {
    $this->seed(\Database\Seeders\RoleSeeder::class);

    $this->admin = User::factory()->create();
    $this->admin->assignRole('admin');

    $this->company = Company::factory()->create();
    $this->manager = User::factory()->create(['company_id' => $this->company->id]);
    $this->manager->assignRole('manager');

    $this->order = Order::factory()->create(['company_id' => $this->company->id]);
});

it('allows admin to dispatch document generation', function () {
    Bus::fake();

    $response = actingAs($this->admin)
        ->post("/orders/{$this->order->order_number}/documents/cmr/generate");

    $response->assertRedirect();
    $response->assertSessionHas('success');

    Bus::assertDispatched(GenerateDocumentJob::class, function ($job) {
        return $job->order->id === $this->order->id &&
               $job->type === 'cmr';
    });
});

it('allows manager to dispatch document generation for their company', function () {
    Bus::fake();

    $response = actingAs($this->manager)
        ->post("/orders/{$this->order->order_number}/documents/invoice/generate");

    $response->assertRedirect();
    Bus::assertDispatched(GenerateDocumentJob::class, function ($job) {
        return $job->order->id === $this->order->id &&
               $job->type === 'invoice';
    });
});
it('forbids manager from generating documents for another company', function () {
    $otherCompany = Company::factory()->create();
    $otherOrder = Order::factory()->create(['company_id' => $otherCompany->id]);

    $response = actingAs($this->manager)
        ->post("/orders/{$otherOrder->order_number}/documents/cmr/generate");

    $response->assertForbidden();
});

it('successfully generates and uploads document in the job', function () {
    Storage::fake('s3');

    $job = new GenerateDocumentJob($this->order, 'cmr', $this->admin->id);
    $job->handle();

    $fileName = "cmr-{$this->order->order_number}.pdf";
    $path = "orders/{$this->order->order_number}/{$fileName}";

    Storage::disk('s3')->assertExists($path);

    $this->assertDatabaseHas('order_documents', [
        'order_id' => $this->order->id,
        'document_type' => 'cmr',
        'path' => $path,
    ]);
});

it('allows downloading an existing document', function () {
    Storage::fake('s3');
    $path = "orders/{$this->order->order_number}/invoice.pdf";
    Storage::disk('s3')->put($path, 'dummy content');

    $document = OrderDocument::create([
        'order_id' => $this->order->id,
        'document_type' => 'invoice',
        'path' => $path,
    ]);

    $response = actingAs($this->manager)
        ->get("/orders/{$this->order->order_number}/documents/{$document->id}/download");

    $response->assertOk();
});

it('forbids downloading document of another company', function () {
    $otherCompany = Company::factory()->create();
    $otherOrder = Order::factory()->create(['company_id' => $otherCompany->id]);
    $document = OrderDocument::create([
        'order_id' => $otherOrder->id,
        'document_type' => 'cmr',
        'path' => 'any.pdf',
    ]);

    $response = actingAs($this->manager)
        ->get("/orders/{$otherOrder->order_number}/documents/{$document->id}/download");

    $response->assertForbidden();
});
