<?php

declare(strict_types=1);

use App\Events\DocumentGenerated;
use App\Jobs\GenerateDocumentJob;
use App\Models\Order;
use App\Models\OrderDocument;
use App\Models\User;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;

it('generates and stores order document and dispatches event', function () {
    Event::fake();
    Storage::fake('s3');

    $user = User::factory()->create();
    $order = Order::factory()->create(['user_id' => $user->id]);

    $job = new GenerateDocumentJob($order, 'cmr', $user->id);
    $job->handle();

    Storage::disk('s3')->assertExists("orders/{$order->order_number}/cmr-{$order->order_number}.pdf");

    expect(OrderDocument::where('order_id', $order->id)->where('document_type', 'cmr')->exists())->toBeTrue();

    Event::assertDispatched(DocumentGenerated::class, function ($event) use ($order, $user) {
        return $event->order->id === $order->id && $event->documentType === 'cmr' && $event->userId === $user->id;
    });
});

it('logs error and rethrows exception when document generation fails', function () {
    Log::shouldReceive('error')->once();

    $user = User::factory()->create();
    $order = Order::factory()->create(['user_id' => $user->id]);

    Pdf::shouldReceive('loadView')->andThrow(new Exception('PDF generation failed'));

    $job = new GenerateDocumentJob($order, 'cmr', $user->id);

    expect(fn () => $job->handle())->toThrow(Exception::class, 'PDF generation failed');
});
