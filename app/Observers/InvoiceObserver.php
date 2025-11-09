<?php

namespace App\Observers;

use App\Models\Invoice;
use App\Services\Form6Generator;
use Illuminate\Support\Facades\Log;

class InvoiceObserver
{
    public function created(Invoice $invoice): void
    {
        $this->maybeGenerateForm6($invoice);
    }

    public function updated(Invoice $invoice): void
    {
        $this->maybeGenerateForm6($invoice);
    }

    protected function maybeGenerateForm6(Invoice $invoice): void
    {
        try {
            if ($invoice->status === 'finalized') {
                $generator = app(Form6Generator::class);
                $doc = $generator->generateOrUpdateForInvoice($invoice);
                Log::info('InvoiceObserver: Form6 generated/updated', ['invoice_id' => $invoice->getKey(), 'form6_id' => $doc->getKey()]);
            }
        } catch (\Throwable $e) {
            Log::error('InvoiceObserver: Form6 generation failed', [
                'invoice_id' => $invoice->getKey(),
                'error' => $e->getMessage(),
            ]);
        }
    }
}
