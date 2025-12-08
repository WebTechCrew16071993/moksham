<?php

namespace App\Http\Controllers;

use App\Models\Form6Document;
use App\Models\CompanySetting;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\Request;

class Form6PrintController extends Controller
{
    public function show(Request $request, Form6Document $form6)
    {
        $form6->load(['shipment','invoice','bl','packingList']);
        // Ensure signature fields are populated like Form 9 defaults
        $setting = CompanySetting::query()->first();
        if (empty($form6->exporter_signature_image_path) && $setting?->company_signed_logo) {
            $form6->exporter_signature_image_path = $setting->company_signed_logo;
        }
        if (empty($form6->importer_signature_image_path) && $setting?->company_signed_logo) {
            $form6->importer_signature_image_path = $setting->company_signed_logo;
        }
        if (empty($form6->exporter_signature_name) && $setting?->company_signature_text) {
            $form6->exporter_signature_name = $setting->company_signature_text;
        }
        $asPdf = false;
        return view('form6.print', compact('form6','asPdf'));
    }

    public function pdf(Request $request, Form6Document $form6)
    {
        $form6->load(['shipment','invoice','bl','packingList']);
        // Ensure signature fields are populated like Form 9 defaults
        $setting = CompanySetting::query()->first();
        if (empty($form6->exporter_signature_image_path) && $setting?->company_signed_logo) {
            $form6->exporter_signature_image_path = $setting->company_signed_logo;
        }
        if (empty($form6->importer_signature_image_path) && $setting?->company_signed_logo) {
            $form6->importer_signature_image_path = $setting->company_signed_logo;
        }
        if (empty($form6->exporter_signature_name) && $setting?->company_signature_text) {
            $form6->exporter_signature_name = $setting->company_signature_text;
        }
        $asPdf = true;
        $pdf = Pdf::loadView('form6.print', compact('form6','asPdf'))->setPaper('a4');
        $fileName = 'form6_'.$form6->form6_no.'.pdf';
        if ($request->boolean('download')) {
            return $pdf->download($fileName);
        }
        return $pdf->stream($fileName);
    }
}
