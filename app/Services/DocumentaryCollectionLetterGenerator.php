<?php

namespace App\Services;

use App\Models\DocumentaryCollectionLetter;
use App\Models\Invoice;
use App\Models\CompanySetting;
use Illuminate\Support\Facades\Log;

class DocumentaryCollectionLetterGenerator
{
    public function generateOrUpdateForInvoice(Invoice $invoice): DocumentaryCollectionLetter
    {
        $invoice->loadMissing(['shipment.indent','packingList.items','blCorrection','consignee']);
        $setting = CompanySetting::query()->first();
        $shipment = $invoice->shipment;
        $pl = $invoice->packingList;
        $bl = $invoice->blCorrection;

        $containers = (int) ($invoice->no_of_cont ?: ($pl ? max(1, (int) $pl->items()->distinct('container_no')->count('container_no')) : 1));
        $invYear = optional($invoice->date)->format('Y') ?? now()->format('Y');
        $year2 = (int) substr((string) $invYear, -2);
        $sa = sprintf('%d-%d/%s', $year2, $containers, $invoice->invoice_no);

        $consigneeId = $invoice->consignee?->getKey();

        $doc = DocumentaryCollectionLetter::query()->where('invoice_id', $invoice->getKey())->first();
        $data = [
            'invoice_id' => $invoice->getKey(),
            'shipment_id' => $shipment?->getKey(),
            'packing_list_id' => $pl?->getKey(),
            'bl_correction_id' => $bl?->getKey(),
            'user_id' => $invoice->user_id,
            'letter_no' => $doc?->letter_no ?? (string) (1000 + (DocumentaryCollectionLetter::withTrashed()->max('id') ?? 0) + 1),
            'letter_date' => now()->toDateString(),
            'ref_year' => $year2,
            'ref_container_count' => $containers,
            'ref_invoice_no' => (string) $invoice->invoice_no,
            'sa_contract_no' => $sa,
            'amount_usd' => $invoice->amount,
            'consignee_id' => $consigneeId,
            'signer_name' => $setting?->company_signature_text ?? 'Authorized Signatory',
            'signer_title' => 'C.O.O.',
            'status' => 'draft',
        ];

        if ($doc) { $doc->fill($data); $doc->saveQuietly(); Log::info('DCL updated', ['id'=>$doc->id]); return $doc; }
        $created = DocumentaryCollectionLetter::create($data); Log::info('DCL created', ['id'=>$created->id]); return $created;
    }
}
