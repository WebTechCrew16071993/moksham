<?php

namespace App\Services;

use App\Models\Invoice;
use App\Models\PackingList;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;

class InvoiceGenerator
{
    public function generateOrUpdateForPackingList(PackingList $packingList): Invoice
    {
        Log::info('InvoiceGenerator: start', ['packing_list_id' => $packingList->getKey()]);
        $packingList->loadMissing(['shipment.indent', 'items']);
        $shipment = $packingList->shipment;
        $indent = $shipment?->indent;
        if (!$shipment) {
            Log::warning('InvoiceGenerator: packing list has no shipment, skipping', ['packing_list_id' => $packingList->getKey()]);
        }

        // Extract numeric rate from indent->price using regex like "US$ 200.00" => 200.00
        $rate = null;
        if ($indent && $indent->price) {
            if (preg_match('/([0-9]+(?:\.[0-9]{1,2})?)/', $indent->price, $m)) {
                $rate = (float) $m[1];
            }
        }

        // Prefer persisted total_weight_kg; if missing/zero, sum from items
        $totalKg = (float) ($packingList->total_weight_kg ?? 0);
        $weightSource = 'packing_list.total_weight_kg';
        if ($totalKg <= 0 && $packingList->items->count() > 0) {
            $totalKg = (float) $packingList->items->sum(function ($i) {
                return (float) ($i->weight_kg ?? 0);
            });
            $weightSource = 'sum(items.weight_kg)';
        }
        $weightMt = round($totalKg / 1000, 3);
        $amount = $rate !== null ? round($weightMt * (float) $rate, 2) : null;

        $noOfContCount = $packingList->items->count();
        $noOfCont = $noOfContCount > 0 && $packingList->ship_in
            ? ($noOfContCount . ' x ' . strtoupper((string) $packingList->ship_in))
            : (string) $noOfContCount;

        $desc = "WASTEPAPER OCC 11\nMOISTURE CONTAIN: LESS THAN 12%\n(HS CODE: 47079000)\n(" . number_format($totalKg, 2) . " KGS)";

        // Determine next invoice number starting at 1002 (include soft-deleted)
        // Use numeric ordering to avoid lexical issues like '100' > '99'
        $maxExisting = Invoice::withTrashed()
            ->orderByRaw('CAST(invoice_no AS UNSIGNED) DESC')
            ->value('invoice_no');
        $nextNo = 1002;
        if ($maxExisting && preg_match('/(\d+)/', (string) $maxExisting, $mm)) {
            $nextNo = max(1001, (int) $mm[1]) + 1;
        }
        // Ensure uniqueness even if a row was just created concurrently or exists soft-deleted
        while (Invoice::withTrashed()->where('invoice_no', (string) $nextNo)->exists()) {
            $nextNo++;
        }

        // Find existing invoice for this packing list (1:1)
        $invoice = Invoice::query()->where('packing_list_id', $packingList->getKey())->latest()->first();

        // Compute destination preferring Packing List, then Indent
        $finalDest = $packingList->destination ?: ($indent?->final_destination);

        $data = [
            'packing_list_id'=> $packingList->getKey(),
            'shipment_id'    => $shipment?->getKey(),
            'user_id'        => $packingList->user_id ?? Auth::id(),
            'consignee_id'   => $indent?->consignee_id,
            'date'           => now()->toDateString(),
            'employee'       => $packingList->employee,
            'obl_ref'        => $shipment?->booking_no,
            'no_of_cont'     => $noOfCont,
            'weight_mt'      => $weightMt,
            'description'    => $desc,
            'moisture_contain' => 'LESS THAN 12%',
            'rate_mt'        => $rate,
            'amount'         => $amount,
            'payment_terms'  => $indent?->payment,
            'advance'        => 0,
            'amount_due'     => $amount,
            'return_date'    => $packingList->date ?: $shipment?->booking_date,
            'cut_off_date'   => $packingList->date,
            'dept_est'       => $packingList->ship_date,
            'arrival'        => $packingList->arrival_date,
            'si_cut_off'     => null,
            'f_dest'         => $finalDest,
            'remarks'        => $indent?->remarks,
            'terms_conditions'=> $indent?->other_terms,
            'status'         => 'draft',
        ];

        try {
            Log::info('InvoiceGenerator: computed', [
                'packing_list_id' => $packingList->getKey(),
                'shipment_id' => $shipment?->getKey(),
                'consignee_id' => $indent?->consignee_id,
                'rate_mt' => $rate,
                'weight_source' => $weightSource,
                'weight_mt' => $weightMt,
                'amount' => $amount,
                'no_of_cont' => $noOfCont,
            ]);

            if ($invoice) {
                // Preserve fields the user may have edited manually; only set if currently empty
                $preserveIfSet = ['origin', 'payment_terms', 'remarks', 'terms_conditions'];
                foreach ($preserveIfSet as $key) {
                    if (!empty($invoice->{$key})) {
                        unset($data[$key]);
                    }
                }
                // If origin is empty, try to infer from packing list
                if (empty($invoice->origin)) {
                    $data['origin'] = $packingList->origin ?? null;
                }
                // Always sync final destination from Packing List / Indent preference
                $data['f_dest'] = $finalDest;

                $invoice->fill($data);
                $invoice->saveQuietly();
                Log::info('InvoiceGenerator: updated existing invoice', ['invoice_id' => $invoice->getKey()]);
                return $invoice;
            }

            $data['invoice_no'] = (string) $nextNo;
            // Double-check uniqueness before create (race-condition guard)
            if (Invoice::withTrashed()->where('invoice_no', $data['invoice_no'])->exists()) {
                do { $nextNo++; } while (Invoice::withTrashed()->where('invoice_no', (string) $nextNo)->exists());
                $data['invoice_no'] = (string) $nextNo;
            }
            $created = Invoice::create($data);
            Log::info('InvoiceGenerator: created new invoice', ['invoice_id' => $created->getKey(), 'invoice_no' => $created->invoice_no]);
            return $created;
        } catch (\Throwable $e) {
            Log::error('InvoiceGenerator: failed', [
                'packing_list_id' => $packingList->getKey(),
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);
            throw $e;
        }
    }
}
