<?php

namespace App\Observers;

use App\Models\Invoice;
use App\Services\Form6Generator;
use App\Services\Form9Generator;
use App\Models\Form6Document;
use App\Models\Form9Document;
use Illuminate\Support\Facades\Log;

class InvoiceObserver
{
    // Use the 'saved' event to cover both create and update consistently.
    public function saved(Invoice $invoice): void
    {
        Log::info('InvoiceObserver: invoice saved event', [
            'invoice_id' => $invoice->getKey(),
            'status' => $invoice->status,
        ]);
        
        $this->maybeGenerateForm6($invoice);
    }

    protected function maybeGenerateForm6(Invoice $invoice): void
    {
        try {
            // Whenever an invoice is in finalized state, keep Form 6 and Form 9
            // in sync on both create and any subsequent updates.
            if ($invoice->status === 'finalized') {
                $gen6 = app(Form6Generator::class);
                $doc6 = $gen6->generateOrUpdateForInvoice($invoice);
                Log::info('InvoiceObserver: Form6 synced', ['invoice_id' => $invoice->getKey(), 'form6_id' => $doc6->getKey()]);

                $gen9 = app(Form9Generator::class);
                $doc9 = $gen9->generateOrUpdateForInvoice($invoice);
                Log::info('InvoiceObserver: Form9 synced', ['invoice_id' => $invoice->getKey(), 'form9_id' => $doc9->getKey()]);
            }
        } catch (\Throwable $e) {
            Log::error('InvoiceObserver: sync failed', [
                'invoice_id' => $invoice->getKey(),
                'error' => $e->getMessage(),
            ]);
        }
    }
}
