<?php

namespace App\Http\Controllers;

use App\Models\PackingList;
use App\Models\CompanySetting;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Barryvdh\DomPDF\Facade\Pdf;

class PackingListPrintController extends Controller
{
    public function show(Request $request, PackingList $packingList)
    {
        $packingList->load(['shipment.indent', 'items']);

        // Compute totals just in case
        $totalBales = (int) $packingList->items->sum('no_of_bales');
        $totalWeightLbs = (float) $packingList->items->sum('weight_lbs');
        $totalWeightKg = round($totalWeightLbs * 0.453592, 3);

        $setting = CompanySetting::query()->first();

        $invoice = optional($packingList->shipment)->invoices()->latest()->first();
        $containerCount = $packingList->items->count();
        $descriptionSummary = optional($packingList->items->first())->description;

        return view('packing_lists.print', [
            'packingList' => $packingList,
            'totalBales' => $totalBales,
            'totalWeightLbs' => $totalWeightLbs,
            'totalWeightKg' => $totalWeightKg,
            'setting' => $setting,
            'invoice' => $invoice,
            'containerCount' => $containerCount,
            'descriptionSummary' => $descriptionSummary,
            'asPdf' => false,
        ]);
    }

    public function generate(Request $request, PackingList $packingList)
    {
        // Prepare data (same as show)
        $packingList->load(['shipment.indent', 'items']);
        $setting = CompanySetting::query()->first();
        $invoice = optional($packingList->shipment)->invoices()->latest()->first();
        $totalBales = (int) $packingList->items->sum('no_of_bales');
        $totalWeightLbs = (float) $packingList->items->sum('weight_lbs');
        $totalWeightKg = round($totalWeightLbs * 0.453592, 3);
        $containerCount = $packingList->items->count();
        $descriptionSummary = optional($packingList->items->first())->description;

        $asPdf = true;
        $pdf = Pdf::loadView('packing_lists.print', compact(
            'packingList', 'setting', 'invoice', 'totalBales', 'totalWeightLbs', 'totalWeightKg', 'containerCount', 'descriptionSummary', 'asPdf'
        ))->setPaper('a4');

        $fileName = sprintf('packing_list_%s_%d.pdf', $packingList->shipment?->booking_no ?? 'booking', $packingList->getKey());
        if ($request->boolean('download')) {
            return $pdf->download($fileName);
        }
        // Stream inline in browser
        return $pdf->stream($fileName);
    }
}
