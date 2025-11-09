<?php

namespace App\Http\Controllers;

use App\Models\BlCorrection;
use App\Models\CompanySetting;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\Request;

class BlCorrectionPrintController extends Controller
{
    public function show(Request $request, BlCorrection $bl)
    {
        $bl->load(['shipment.indent', 'packingList', 'items']);
        $setting = CompanySetting::query()->first();

        $asPdf = false;
        return view('bls.print', compact('bl', 'setting', 'asPdf'));
    }

    public function pdf(Request $request, BlCorrection $bl)
    {
        $bl->load(['shipment.indent', 'packingList', 'items']);
        $setting = CompanySetting::query()->first();

        $asPdf = true;
        $pdf = Pdf::loadView('bls.print', compact('bl', 'setting', 'asPdf'))
            ->setPaper('a4');

        $fileName = sprintf('bl_%s_%d.pdf', $bl->booking_no ?? 'booking', $bl->getKey());
        if ($request->boolean('download')) {
            return $pdf->download($fileName);
        }
        return $pdf->stream($fileName);
    }
}
