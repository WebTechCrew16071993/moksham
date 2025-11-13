<?php

namespace App\Http\Controllers;

use App\Models\Form9Document;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\Request;

class Form9PrintController extends Controller
{
    public function show(Request $request, Form9Document $form9)
    {
        $form9->load(['shipment','invoice','bl','packingList','carriers']);
        $asPdf = false;
        return view('form9.print', compact('form9','asPdf'));
    }

    public function pdf(Request $request, Form9Document $form9)
    {
        $form9->load(['shipment','invoice','bl','packingList','carriers']);
        $asPdf = true;
        $pdf = Pdf::loadView('form9.print', compact('form9','asPdf'))->setPaper('a4');
        $fileName = 'form9_'.$form9->form9_no.'.pdf';
        if ($request->boolean('download')) {
            return $pdf->download($fileName);
        }
        return $pdf->stream($fileName);
    }
}
