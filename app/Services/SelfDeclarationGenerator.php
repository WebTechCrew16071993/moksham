<?php

namespace App\Services;

use App\Models\SelfDeclaration;
use App\Models\Invoice;
use App\Models\CompanySetting;
use Illuminate\Support\Facades\Log;

class SelfDeclarationGenerator
{
    public function generateOrUpdateForInvoice(Invoice $invoice): SelfDeclaration
    {
        $invoice->loadMissing(['shipment.indent','packingList.items','blCorrection','consignee']);
        $setting = CompanySetting::query()->first();
        $shipment = $invoice->shipment;
        $pl = $invoice->packingList ?: ($shipment ? $shipment->packingLists()->latest('id')->first() : null);
        $bl = $invoice->blCorrection ?: ($pl ? $pl->blCorrections()->latest('id')->first() : null);
        $indent = $shipment?->indent;

        $containers = (int) ($invoice->no_of_cont ?: ($pl ? max(1, (int) $pl->items()->distinct('container_no')->count('container_no')) : 1));
        $invYear = optional($invoice->date)->format('Y') ?? now()->format('Y');
        $year2 = (int) substr((string) $invYear, -2);
        $certNo = sprintf('%d-%d/%s', $year2, $containers, $invoice->invoice_no);

        $importerDetails = trim(implode("\n", array_filter([
            $invoice->consignee?->name ?? $indent?->consignee_name,
            $indent?->consignee_address,
            trim(($indent?->consignee_city.' '.$indent?->consignee_state.' '.$indent?->consignee_zip)),
            $indent?->consignee_country,
            $indent?->consignee_email,
        ])));

        $quantityKgs = 0.0;
        if ($pl) {
            $quantityKgs = (float) ($pl->total_weight_kg ?: 0);
            if ($quantityKgs <= 0) {
                $quantityKgs = (float) $pl->items()->sum('weight_kg');
            }
        }

        $doc = SelfDeclaration::query()->where('invoice_id', $invoice->getKey())->first();
        $data = [
            'invoice_id' => $invoice->getKey(),
            'shipment_id' => $shipment?->getKey(),
            'packing_list_id' => $pl?->getKey(),
            'bl_correction_id' => $bl?->getKey(),
            'user_id' => $invoice->user_id,
            'ref_year' => $year2,
            'ref_container_count' => $containers,
            'ref_invoice_no' => (string) $invoice->invoice_no,
            'certificate_number' => $certNo,
            'issue_date' => (optional($invoice->date)->toDateString()) ?? now()->toDateString(),
            'invoice_no' => (string) $invoice->invoice_no,
            'importer_details' => $importerDetails,
            'goods_description' => $indent?->product_description ?: 'WASTEPAPER OCC 11',
            'total_quantity_kgs' => $quantityKgs,
            'declaration_points' => [
                'v' => 'The consignment is actually waste paper as per internationally acceptable parameters.',
                'vi' => 'There is no putrefiable organic matter in this consignment.',
                'vii' => 'The approximate content of non paper material is less than 5%.',
                'viii' => 'No municipal solid waste or hazardous waste is part of this consignment.',
            ],
            'signer_name' => $setting?->company_signature_text ?? 'Authorised Signatory',
            'signer_title' => 'MOKSHAM EXPORT IMPORT LLC.',
            'status' => 'draft',
        ];

        if ($doc) { $doc->fill($data); $doc->saveQuietly(); Log::info('SelfDeclaration updated', ['id'=>$doc->id]); return $doc; }
        $created = SelfDeclaration::create($data); Log::info('SelfDeclaration created', ['id'=>$created->id]); return $created;
    }
}
