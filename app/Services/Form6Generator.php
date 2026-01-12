<?php

namespace App\Services;

use App\Models\Form6Document;
use App\Models\Invoice;
use App\Models\CompanySetting;
use Illuminate\Support\Facades\Log;

class Form6Generator
{
    public function generateOrUpdateForInvoice(Invoice $invoice): Form6Document
    {
        $invoice->loadMissing(['shipment.indent', 'packingList', 'blCorrection']);
        $shipment = $invoice->shipment;
        $pl = $invoice->packingList ?: ($shipment ? $shipment->packingLists()->latest('id')->first() : null);
        $bl = $invoice->blCorrection ?: ($pl ? $pl->blCorrections()->latest('id')->first() : null);
        $indent = $shipment?->indent;

        $setting = CompanySetting::query()->first();
        $exporterAddress = trim(implode("\n", array_filter([
            $setting?->company_name,
            $setting?->company_address,
            trim(($setting?->company_city.' '.$setting?->company_state.' '.$setting?->company_zip)),
            $setting?->company_country,
        ])));

        $generatorAddress = $exporterAddress; // same as exporter per sample
        $importerAddress = trim(implode("\n", array_filter([
            $indent?->consignee_name,
            $indent?->consignee_address,
            trim(($indent?->consignee_city.' '.$indent?->consignee_state.' '.$indent?->consignee_zip)),
            $indent?->consignee_country,
        ])));

        $quantityKgs = (float) (($pl?->total_weight_kg) ?: 0);
        if ($quantityKgs <= 0 && $pl) {
            $quantityKgs = (float) $pl->items()->sum('weight_kg');
        }

        // Find existing Form6 by invoice
        $doc = Form6Document::query()->where('invoice_id', $invoice->getKey())->first();
        $data = [
            'shipment_id' => $shipment?->getKey(),
            'packing_list_id' => $pl?->getKey(),
            'bl_correction_id' => $bl?->getKey(),
            'invoice_id' => $invoice->getKey(),
            'user_id' => $invoice->user_id,
            'form6_no' => $doc?->form6_no ?? (string) (1000 + (Form6Document::withTrashed()->max('id') ?? 0) + 1),
            'form6_date' => now()->toDateString(),
            'applicant_ref_no' => $invoice->invoice_no,
            'exporter_name_address' => $exporterAddress,
            'exporter_contact_person' => $setting?->company_signature_text ?? 'VIKAS PATEL',
            'exporter_phone' => $setting?->company_phone,
            'exporter_email' => $setting?->company_email,
            'generator_name_address' => $generatorAddress,
            'generator_contact_person' => $setting?->company_signature_text ?? 'VIKAS PATEL',
            'generator_phone' => $setting?->company_phone,
            'generator_email' => $setting?->company_email,
            'site_of_generation' => null,
            'importer_id' => $indent?->consignee_id,
            'importer_name_address' => $importerAddress,
            'importer_contact_person' => $indent?->kind_attention ?: 'N.A.',
            'importer_phone' => $indent?->consignee_phone ?? null,
            'importer_email' => $indent?->consignee_email ?? null,
            'bill_of_lading' => $bl?->booking_no ?? $bl?->bl_no ?? null,
            'country_of_export' => 'United States',
            'country_of_import' => 'India',
            'quantity_kgs' => $quantityKgs,
            'physical_characteristics' => 'Solid',
            'chemical_composition' => 'EN 643',
            'basel_no' => 'B3020',
            'un_shipping_name' => 'N.A.',
            'un_class' => 'N.A.',
            'un_no' => 'N.A.',
            'h_number' => 'N.A.',
            'y_number' => 'N.A.',
            'itc_hs' => $indent?->hsn_code ?: '4707.90',
            'customs_code_hs' => $indent?->hsn_code ?: '47079000',
            'other_codes' => 'N.A.',
            'package_type' => 'BALES',
            'package_number' => (int) ($pl?->total_bales ?? 0),
            'special_handling_requirements' => 'N.A.',
            'movement_type' => 'single',
            'means_of_transport' => 'sea',
            'date_of_transfer' => $shipment?->booking_date?->toDateString(),
            'exporter_declaration' => 'N.A.',
            'exporter_signature_name' => $setting?->company_signature_text ?? 'VIKAS PATEL',
            'exporter_signature_date' => now()->toDateString(),
            'exporter_signature_image_path' => $setting?->company_signed_logo,
            // Importer certification auto-filled from company settings as well
            'importer_certification_signature' => $setting?->company_signature_text ?? 'N.A.',
            'importer_certification_date' => now()->toDateString(),
            'importer_signature_image_path' => $setting?->company_signed_logo,
            'notes' => "(1) Attach list, if more than one; (2) Select appropriate option; (3) Immediately contact competent authority in case of any\nemergency; (4) If more than one transporter carriers, attach information as required in SL. No. 12",
            'status' => 'draft',
        ];

        if ($doc) {
            $doc->fill($data);
            $doc->saveQuietly();
            Log::info('Form6Generator: updated', ['form6_id' => $doc->getKey()]);
            return $doc;
        }
        $created = Form6Document::create($data);
        Log::info('Form6Generator: created', ['form6_id' => $created->getKey()]);
        return $created;
    }
}
