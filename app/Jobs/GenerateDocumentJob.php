<?php

namespace App\Jobs;

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
        public string $type // 'cmr' or 'invoice'
    ) {}

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        $viewName = "pdfs.{$this->type}";
        $fileName = "{$this->type}-{$this->order->order_number}.pdf";
        $path = "orders/{$this->order->order_number}/{$fileName}";

        $pdf = Pdf::loadView($viewName, [
            'order' => $this->order->load(['company', 'items'])
        ]);

        Storage::disk('s3')->put($path, $pdf->output());

        OrderDocument::updateOrCreate(
            ['order_id' => $this->order->id, 'document_type' => $this->type],
            ['path' => $path]
        );
    }
}
