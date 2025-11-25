<?php

namespace App\Services;

use App\Models\BillOfExchange;
use App\Models\Invoice;
use App\Models\CompanySetting;
use Illuminate\Support\Facades\Log;

class BillOfExchangeGenerator
{
    public function generateOrUpdateForInvoice(Invoice $invoice): BillOfExchange
    {
        $invoice->loadMissing(['shipment.indent','packingList.items','blCorrection','consignee']);
        $setting = CompanySetting::query()->first();
        $shipment = $invoice->shipment;
        $pl = $invoice->packingList;
        $bl = $invoice->blCorrection;

        $containers = (int) ($invoice->no_of_cont ?: ($pl ? max(1, (int) $pl->items()->distinct('container_no')->count('container_no')) : 1));
        $invYear = optional($invoice->date)->format('Y') ?? now()->format('Y');
        $year2 = (int) substr((string) $invYear, -2);
        $ref = sprintf('%d-%d/%s', $year2, $containers, $invoice->invoice_no);

        $draweeName = $invoice->consignee?->name ?? $invoice->consignee?->company_name ?? null;
        $draweeAddress = trim(implode("\n", array_filter([
            $invoice->consignee?->address,
            trim(($invoice->consignee?->city.' '.$invoice->consignee?->state.' '.$invoice->consignee?->zip)),
            $invoice->consignee?->country,
        ])));
        $draweeAddress = $draweeAddress !== '' ? $draweeAddress : null;

        $amount = (float) $invoice->amount;
        $amountWords = $this->numberToWordsUsd($amount);

        $doc = BillOfExchange::query()->where('invoice_id', $invoice->getKey())->first();
        $data = [
            'invoice_id' => $invoice->getKey(),
            'shipment_id' => $shipment?->getKey(),
            'packing_list_id' => $pl?->getKey(),
            'bl_correction_id' => $bl?->getKey(),
            'user_id' => $invoice->user_id,
            'ref_year' => $year2,
            'ref_container_count' => $containers,
            'ref_invoice_no' => (string) $invoice->invoice_no,
            'ref_no' => $ref,
            'issue_date' => (optional($invoice->date)->toDateString()) ?? now()->toDateString(),
            'place_of_issue' => 'UNITED STATES',
            'amount_usd' => $amount,
            'amount_in_words' => $amountWords,
            'drawee_name' => $draweeName,
            'drawee_address' => $draweeAddress,
            'issuer_name' => $setting?->company_name,
            'issuer_title' => 'Authorised Signature',
            'status' => 'draft',
        ];

        if ($doc) { $doc->fill($data); $doc->saveQuietly(); Log::info('BOE updated', ['id'=>$doc->id]); return $doc; }
        $created = BillOfExchange::create($data); Log::info('BOE created', ['id'=>$created->id]); return $created;
    }

    protected function numberToWordsUsd(float $amount): string
    {
        $whole = floor($amount);
        $cents = (int) round(($amount - $whole) * 100);
        $words = ucwords(trim($this->toWords($whole)));
        $parts = $words ? "USD {$words}" : 'USD Zero';
        return $parts;
    }

    protected function toWords(int $num): string
    {
        $ones = ['','one','two','three','four','five','six','seven','eight','nine','ten','eleven','twelve','thirteen','fourteen','fifteen','sixteen','seventeen','eighteen','nineteen'];
        $tens = ['','','twenty','thirty','forty','fifty','sixty','seventy','eighty','ninety'];
        if ($num < 20) return $ones[$num];
        if ($num < 100) return $tens[intdiv($num,10)] . ($num%10? ' ' . $ones[$num%10] : '');
        if ($num < 1000) return $ones[intdiv($num,100)] . ' hundred' . (($num%100)? ' ' . $this->toWords($num%100) : '');
        if ($num < 1000000) return $this->toWords(intdiv($num,1000)) . ' thousand' . (($num%1000)? ' ' . $this->toWords($num%1000) : '');
        if ($num < 1000000000) return $this->toWords(intdiv($num,1000000)) . ' million' . (($num%1000000)? ' ' . $this->toWords($num%1000000) : '');
        return (string)$num;
    }
}
