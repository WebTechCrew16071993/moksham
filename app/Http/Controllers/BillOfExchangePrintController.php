<?php

namespace App\Http\Controllers;

use App\Models\BillOfExchange;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\Request;

class BillOfExchangePrintController extends Controller
{
    public function show(Request $request, BillOfExchange $boe)
    {
        $boe->load(['invoice.consignee','shipment','packingList','blCorrection']);
        $setting = \App\Models\CompanySetting::query()->first();
        $asPdf = false;
        return view('boe.print', compact('boe','setting','asPdf'));
    }

    public function pdf(Request $request, BillOfExchange $boe)
    {
        $boe->load(['invoice.consignee','shipment','packingList','blCorrection']);
        $setting = \App\Models\CompanySetting::query()->first();
        $asPdf = true;
        $pdf = Pdf::loadView('boe.print', compact('boe','setting','asPdf'))->setPaper('a4');
        $rawName = 'boe_'.($boe->ref_no ?: $boe->id);
        $safeName = preg_replace('/[\\\\\/]+/', '-', $rawName);
        $fileName = $safeName.'.pdf';
        if ($request->boolean('download')) {
            return $pdf->download($fileName);
        }
        return $pdf->stream($fileName);
    }
}
