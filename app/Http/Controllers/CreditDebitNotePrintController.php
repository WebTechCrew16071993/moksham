<?php

namespace App\Http\Controllers;

use App\Models\CreditDebitNote;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\Request;

class CreditDebitNotePrintController extends Controller
{
    public function show(Request $request, CreditDebitNote $note)
    {
        $note->load(['invoice.consignee', 'consignee']);
        $asPdf = false;
        return view('cdn.Credit_Debit_Note_print', [
            'note' => $note,
            'invoice' => $note->invoice,
            'asPdf' => $asPdf,
        ]);
    }

    public function pdf(Request $request, CreditDebitNote $note)
    {
        $note->load(['invoice.consignee', 'consignee']);
        $asPdf = true;
        $pdf = Pdf::loadView('cdn.Credit_Debit_Note_print', [
            'note' => $note,
            'invoice' => $note->invoice,
            'asPdf' => $asPdf,
        ])->setPaper('a4');

        $fileName = sprintf('%s-note_%s.pdf', $note->type, $note->note_no);
        if ($request->boolean('download')) {
            return $pdf->download($fileName);
        }
        return $pdf->stream($fileName);
    }
}
