<?php

declare(strict_types=1);

namespace App\Jobs;

use App\Events\DocumentGenerated;
use App\Models\Order;
use App\Models\OrderDocument;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Support\Facades\Storage;

class GenerateDocumentJob implements ShouldQueue
{
    use Queueable;

    /**
     * Create a new job instance.
     */
    public function __construct(
        public Order $order,
        public string $type, // 'cmr' or 'invoice'
        public int $userId
    ) {}

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        $viewName = "pdfs.{$this->type}";
        $fileName = "{$this->type}-{$this->order->order_number}.pdf";
        $path = "orders/{$this->order->order_number}/{$fileName}";

        try {
            // Load necessary relations for PDF rendering
            $this->order->load(['company', 'user', 'items']);

            $pdf = Pdf::loadView($viewName, [
                'order' => $this->order,
            ]);

            $output = $pdf->output();

            // Store the PDF on the configured disk (usually s3/minio)
            Storage::disk('s3')->put($path, $output);

            OrderDocument::updateOrCreate(
                ['order_id' => $this->order->id, 'document_type' => $this->type],
                ['path' => $path]
            );

            // Notify the user that the document is ready via WebSockets
            DocumentGenerated::dispatch($this->order, $this->type, $this->userId);

        } catch (\Exception $e) {
            \Log::error("Failed to generate or upload document ({$this->type}) for Order #{$this->order->order_number}: ".$e->getMessage());
            throw $e;
        }
    }
}
