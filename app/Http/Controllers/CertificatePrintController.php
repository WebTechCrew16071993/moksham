<?php

namespace App\Http\Controllers;

use App\Models\CertificateOfOrigin;
use App\Models\CompanySetting;
use App\Models\PackingList;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\Request;

class CertificatePrintController extends Controller
{
    public function show(Request $request, CertificateOfOrigin $certificate)
    {
        $certificate->load(['shipment.indent', 'items']);
        $setting = CompanySetting::query()->first();

        // Compute dynamic context from the exact packing list if linked; otherwise fallback to latest by shipment
        $shipment = $certificate->shipment;
        $packingList = $certificate->packingList ?: ($shipment?->packingLists()->with('items')->latest()->first());

        $computed = $this->computeFromPackingList($packingList);

        $asPdf = false;
        return view('certificates.print', compact('certificate', 'setting', 'computed', 'asPdf'));
    }

    public function pdf(Request $request, CertificateOfOrigin $certificate)
    {
        $certificate->load(['shipment.indent', 'items']);
        $setting = CompanySetting::query()->first();

        $shipment = $certificate->shipment;
        $packingList = $certificate->packingList ?: ($shipment?->packingLists()->with('items')->latest()->first());
        $computed = $this->computeFromPackingList($packingList);

        $asPdf = true;
        $pdf = Pdf::loadView('certificates.print', compact('certificate', 'setting', 'computed', 'asPdf'))
            ->setPaper('a4');

        $fileName = sprintf('certificate_of_origin_%s.pdf', $certificate->document_no);
        if ($request->boolean('download')) {
            return $pdf->download($fileName);
        }
        return $pdf->stream($fileName);
    }

    private function computeFromPackingList(?PackingList $pl): array
    {
        if (!$pl) {
            return [
                'total_bales' => null,
                'total_weight_kg' => null,
                'container_count' => null,
                'containers_summary' => null,
                'items' => [],
            ];
        }

        $totalBales = (int) $pl->items->sum('no_of_bales');
        // Prefer stored kg per item; if missing, convert from lbs
        $totalWeightKg = (float) $pl->items->sum(function ($i) {
            if (!is_null($i->weight_kg)) return (float) $i->weight_kg;
            return round(((float) ($i->weight_lbs ?? 0)) * 0.453592, 3);
        });
        $containerCount = $pl->items->count();
        $shipIn = strtoupper((string) $pl->ship_in);
        $containersSummary = !empty($pl->container_no_summary)
            ? $pl->container_no_summary
            : ($containerCount > 0 && $shipIn ? ($containerCount . ' x ' . $shipIn) : null);

        $items = $pl->items->map(function ($i) {
            return [
                'marks_numbers' => $i->container_no,
                'number_of_packages' => $i->no_of_bales,
                'description' => $i->description,
                'gross_weight_kg' => !is_null($i->weight_kg)
                    ? (float) $i->weight_kg
                    : round(((float) ($i->weight_lbs ?? 0)) * 0.453592, 3),
            ];
        })->all();

        return compact('totalBales', 'totalWeightKg', 'containerCount', 'containersSummary', 'items');
    }
}
