<?php

namespace App\Http\Controllers;

use App\Models\Invoice;
use App\Models\PackingList;
use App\Services\InvoiceGenerator;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\Request;

class InvoicePrintController extends Controller
{
    public function show(Request $request, Invoice $invoice)
    {
        $invoice->load(['shipment.indent', 'consignee']);
        $setting = \App\Models\CompanySetting::query()->first();
        $asPdf = false;
        return view('invoices.print', compact('invoice', 'setting', 'asPdf'));
    }

    public function pdf(Request $request, Invoice $invoice)
    {
        $invoice->load(['shipment.indent', 'consignee']);
        $setting = \App\Models\CompanySetting::query()->first();
        $asPdf = true;
        $pdf = Pdf::loadView('invoices.print', compact('invoice', 'setting', 'asPdf'))
            ->setPaper('a4');

        $fileName = sprintf('invoice_%s.pdf', $invoice->invoice_no);
        if ($request->boolean('download')) {
            return $pdf->download($fileName);
        }
        return $pdf->stream($fileName);
    }

    public function generateForPackingList(Request $request, PackingList $packingList, InvoiceGenerator $generator)
    {
        $invoice = $generator->generateOrUpdateForPackingList($packingList);
        // Redirect to PDF view
        return redirect()->route('invoices.pdf', ['invoice' => $invoice->getKey(), 'download' => 0]);
    }
}
