<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Jobs\GenerateDocumentJob;
use App\Models\Order;
use App\Models\OrderDocument;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Storage;
use Symfony\Component\HttpFoundation\StreamedResponse;

class OrderDocumentController extends Controller
{
    /**
     * Start the document generation process (CMR or Invoice) in the background.
     */
    public function generate(Request $request, Order $order, string $type): RedirectResponse
    {
        // Use custom policy methods for different document types
        if ($type === 'cmr') {
            $this->authorize('generateCmr', $order);
        } elseif ($type === 'invoice') {
            $this->authorize('generateInvoice', $order);
        } else {
            abort(404);
        }

        GenerateDocumentJob::dispatch($order, $type, auth()->id());

        return back()->with('success', 'Document generation started in the background.');
    }

    /**
     * Download or view the document.
     */
    public function download(Request $request, Order $order, OrderDocument $document): Response|StreamedResponse
    {
        if ($document->order_id !== $order->id) {
            abort(404);
        }

        if ($document->document_type === 'cmr') {
            $this->authorize('generateCmr', $order);
        } else {
            $this->authorize('generateInvoice', $order);
        }

        $action = $request->query('action', 'download');

        if ($action === 'view') {
            $file = Storage::disk('s3')->get($document->path);

            return response($file, 200, [
                'Content-Type' => 'application/pdf',
                'Content-Disposition' => 'inline; filename="'.basename($document->path).'"',
            ]);
        }

        return Storage::disk('s3')->download($document->path);
    }
}
