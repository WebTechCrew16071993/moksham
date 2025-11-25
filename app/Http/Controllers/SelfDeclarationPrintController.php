<?php

namespace App\Http\Controllers;

use App\Models\SelfDeclaration;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\Request;

class SelfDeclarationPrintController extends Controller
{
    public function show(Request $request, SelfDeclaration $doc)
    {
        $doc->load(['invoice.consignee','shipment','packingList.items','blCorrection.items']);
        $setting = \App\Models\CompanySetting::query()->first();
        $asPdf = false;
        [$containers, $col1, $col2] = $this->prepareContainers($doc);
        $issueDate = optional($doc->issue_date)->format('d/m/Y') ?? now()->format('d/m/Y');
        return view('self_declaration.print', compact('doc','setting','asPdf','containers','col1','col2','issueDate'));
    }

    public function pdf(Request $request, SelfDeclaration $doc)
    {
        $doc->load(['invoice.consignee','shipment','packingList.items','blCorrection.items']);
        $setting = \App\Models\CompanySetting::query()->first();
        $asPdf = true;
        [$containers, $col1, $col2] = $this->prepareContainers($doc);
        $issueDate = optional($doc->issue_date)->format('d/m/Y') ?? now()->format('d/m/Y');
        $pdf = Pdf::loadView('self_declaration.print', compact('doc','setting','asPdf','containers','col1','col2','issueDate'))->setPaper('a4');
        $rawName = 'self_declaration_'.($doc->certificate_number ?: $doc->id);
        $safeName = str_replace(['/', '\\'], '-', $rawName);
        $fileName = $safeName.'.pdf';
        if ($request->boolean('download')) {
            return $pdf->download($fileName);
        }
        return $pdf->stream($fileName);
    }

    protected function prepareContainers(SelfDeclaration $doc): array
    {
        // Prefer BL items and their weights; fallback to PL item weights
        $pairs = collect();
        if ($doc->blCorrection) {
            $pairs = $doc->blCorrection->items()
                ->select('container_no', 'weight_kgs')
                ->get()
                ->groupBy('container_no')
                ->map(fn($g) => (float) $g->sum('weight_kgs'));
        }
        if ($pairs->isEmpty() && $doc->packingList) {
            $pairs = $doc->packingList->items()
                ->select('container_no', 'weight_kg')
                ->get()
                ->groupBy('container_no')
                ->map(fn($g) => (float) $g->sum('weight_kg'));
        }

        // Build a normalized list of up to 12 containers with quantity
        $rows = $pairs
            ->filter(function ($qty, $cn) { return filled($cn); })
            ->map(function ($qty, $cn) {
                return [
                    'container_no' => trim((string) $cn),
                    'qty' => number_format((float) $qty, 3) . ' KGS',
                ];
            })
            ->values()
            ->take(12)
            ->all();

        // Pad to 12 entries for layout symmetry
        for ($i = count($rows); $i < 12; $i++) {
            $rows[] = ['container_no' => '', 'qty' => ''];
        }

        $left = array_slice($rows, 0, 6);
        $right = array_slice($rows, 6, 6);
        return [$rows, $left, $right];
    }
}
