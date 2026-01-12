<?php

namespace App\Services;

use App\Models\Form9Document;
use App\Models\Invoice;
use App\Models\CompanySetting;
use Illuminate\Support\Facades\Log;

class Form9Generator
{
    public function generateOrUpdateForInvoice(Invoice $invoice): Form9Document
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

        $generatorAddress = $exporterAddress; // same for now
        $importerAddress = trim(implode("\n", array_filter([
            $indent?->consignee_name,
            $indent?->consignee_address,
            trim(($indent?->consignee_city.' '.$indent?->consignee_state.' '.$indent?->consignee_zip)),
            $indent?->consignee_country,
        ])));

        $quantityKgs = (float) ($pl?->total_weight_kg ?: 0);
        if ($quantityKgs <= 0 && $pl) {
            $quantityKgs = (float) $pl->items()->sum('weight_kg');
        }

        $doc = Form9Document::query()->where('invoice_id', $invoice->getKey())->first();
        $data = [
            'shipment_id' => $shipment?->getKey(),
            'packing_list_id' => $pl?->getKey(),
            'bl_correction_id' => $bl?->getKey(),
            'invoice_id' => $invoice->getKey(),
            'user_id' => $invoice->user_id,
            'form9_no' => $doc?->form9_no ?? (string) (1000 + (Form9Document::withTrashed()->max('id') ?? 0) + 1),
            'form9_date' => now()->toDateString(),
            'exporter_name_address' => $exporterAddress,
            'exporter_contact_person' => $setting?->company_signature_text ?? 'VIKAS PATEL',
            'exporter_phone' => $setting?->company_phone,
            'exporter_email' => $setting?->company_email,
            'generator_name_address' => $generatorAddress,
            'generator_contact_person' => $setting?->company_signature_text ?? 'VIKAS PATEL',
            'generator_phone' => $setting?->company_phone,
            'generator_email' => $setting?->company_email,
            'importer_id' => $indent?->consignee_id,
            'importer_name_address' => $importerAddress,
            'importer_contact_person' => $indent?->kind_attention ?: 'N.A.',
            'importer_phone' => $indent?->consignee_phone ?? null,
            'importer_email' => $indent?->consignee_email ?? null,
            'bill_of_lading' => $bl?->booking_no ?? $bl?->bl_no ?? null,
            // Disposer (use importer info by default)
            'disposer_name_address' => $importerAddress,
            'disposer_contact_person' => $indent?->kind_attention ?: 'N.A.',
            'actual_site_of_disposal' => 'SAME AS JUST ABOVE',
            'disposer_phone' => $indent?->consignee_phone ?? null,
            'disposer_email' => $indent?->consignee_email ?? null,

            // Recovery details
            'method_of_recovery' => 'PAPER MANUFACTURING',
            'r_code' => 'R3',
            'technology_employed' => 'CONVENTIONAL WASTEPAPER RECYCLING',

            // Waste & packaging
            'waste_designation_composition' => 'EN 643',
            'physical_characteristics' => 'Solid',
            'actual_quantity_kgs' => $quantityKgs,
            'waste_description' => 'WASTEPAPER OCC 11',
            'basel_no' => 'B3020',
            'itc_hs' => $indent?->hsn_code ?: '4707.90',
            'customs_code_hs' => $indent?->hsn_code ?: '47079000',
            'packaging_type' => 'BALES',
            'packaging_number' => (int) ($pl?->total_bales ?? 0),

            // Shipment
            'means_of_transport' => 'S',
            'actual_shipment_date' => ($shipment && $shipment->booking_date)
                ? \Illuminate\Support\Carbon::parse($shipment->booking_date)->toDateString()
                : now()->toDateString(),

            // Exporter declaration
            'exporter_declaration' => 'N.A.',
            'exporter_declaration_date' => now()->toDateString(),
            'exporter_signature_name' => $setting?->company_signature_text ?? 'VIKAS PATEL',
            'exporter_signature_image_path' => $setting?->company_signed_logo,
            'status' => 'draft',
        ];

        if ($doc) {
            $doc->fill($data);
            $doc->saveQuietly();
            Log::info('Form9Generator: updated', ['form9_id' => $doc->getKey()]);
            return $doc;
        }
        $created = Form9Document::create($data);
        Log::info('Form9Generator: created', ['form9_id' => $created->getKey()]);
        return $created;
    }
}
