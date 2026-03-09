<?php

namespace App\Http\Controllers;

use App\Jobs\GenerateDocumentJob;
use App\Models\Order;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;

class OrderDocumentController extends Controller
{
    public function generate(Request $request, Order $order, string $type): RedirectResponse
    {
        if ($type === 'cmr') {
            Gate::authorize('generateCmr', $order);
        } elseif ($type === 'invoice') {
            Gate::authorize('generateInvoice', $order);
        } else {
            abort(404);
        }

        GenerateDocumentJob::dispatch($order, $type);

        return back()->with('success', 'Document generation started in the background.');
    }

    public function download(Request $request, Order $order, \App\Models\OrderDocument $document)
    {
        if ($document->order_id !== $order->id) {
            abort(404);
        }

        if ($document->document_type === 'cmr') {
            Gate::authorize('generateCmr', $order);
        } else {
            Gate::authorize('generateInvoice', $order);
        }

        $action = $request->query('action', 'download');

        if ($action === 'view') {
            $file = \Illuminate\Support\Facades\Storage::disk('s3')->get($document->path);

            return response($file, 200, [
                'Content-Type' => 'application/pdf',
                'Content-Disposition' => 'inline; filename="'.basename($document->path).'"',
            ]);
        }

        return \Illuminate\Support\Facades\Storage::disk('s3')->download($document->path);
    }
}
