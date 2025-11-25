<?php

namespace App\Http\Controllers;

use App\Models\DocumentaryCollectionLetter;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\Request;

class DocumentaryCollectionLetterPrintController extends Controller
{
    public function show(Request $request, DocumentaryCollectionLetter $letter)
    {
        $letter->load(['invoice.consignee','shipment','packingList','blCorrection']);
        $setting = \App\Models\CompanySetting::query()->first();
        $asPdf = false;
        return view('dcl.print', compact('letter','setting','asPdf'));
    }

    public function pdf(Request $request, DocumentaryCollectionLetter $letter)
    {
        $letter->load(['invoice.consignee','shipment','packingList','blCorrection']);
        $setting = \App\Models\CompanySetting::query()->first();
        $asPdf = true;
        $pdf = Pdf::loadView('dcl.print', compact('letter','setting','asPdf'))->setPaper('a4');
        $rawName = 'dcl_'.($letter->letter_no ?: $letter->id);
        $safeName = preg_replace('/[\\\\\/]+/', '-', $rawName);
        $fileName = $safeName.'.pdf';
        if ($request->boolean('download')) {
            return $pdf->download($fileName);
        }
        return $pdf->stream($fileName);
    }
}
