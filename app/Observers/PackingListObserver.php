<?php

namespace App\Observers;

use App\Models\Invoice;
use App\Models\PackingList;
use App\Services\CertificateGenerator;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;

class PackingListObserver
{
    public function created(PackingList $packingList): void
    {
        $this->syncInvoice($packingList);
        $this->syncCertificate($packingList);
    }

    public function updated(PackingList $packingList): void
    {
        $this->syncInvoice($packingList);
        $this->syncCertificate($packingList);
    }

    protected function syncInvoice(PackingList $packingList): void
    {
        Log::info('PackingListObserver: syncInvoice triggered', ['packing_list_id' => $packingList->getKey()]);
        $packingList->loadMissing(['shipment.indent', 'items']);
        $shipment = $packingList->shipment;
        if (!$shipment) {
            Log::warning('PackingListObserver: No shipment found for packing list', ['packing_list_id' => $packingList->getKey()]);
            return;
        }
        $indent = $shipment->indent;

        // Extract numeric rate from indent->price using regex
        $rate = null;
        if ($indent && $indent->price) {
            if (preg_match('/([0-9]+(?:\.[0-9]{1,2})?)/', $indent->price, $m)) {
                $rate = (float) $m[1];
            }
        }
        Log::info('PackingListObserver: Parsed rate from indent', [
            'indent_id' => $indent?->getKey(),
            'price_raw' => $indent?->price,
            'rate_mt' => $rate,
        ]);
        $weightMt = round(((float) ($packingList->total_weight_kg ?? 0)) / 1000, 3);
        $amount = $rate !== null ? round($weightMt * $rate, 2) : null;
        Log::info('PackingListObserver: Computed invoice totals', [
            'total_weight_kg' => (float) ($packingList->total_weight_kg ?? 0),
            'weight_mt' => $weightMt,
            'amount' => $amount,
        ]);

        // Count containers
        $noOfCont = $packingList->items->count();

        // Description
        $desc = "WASTEPAPER OCC 11\nMOISTURE CONTAIN: LESS THAN 12%\n(HS CODE: 47079000)\n(" . number_format((float) ($packingList->total_weight_kg ?? 0), 2) . " KGS)";

        // Compute next invoice number (string) starting at 1002
        // Determine next invoice number including soft-deleted rows
        $maxExisting = Invoice::withTrashed()
            ->orderByRaw('CAST(invoice_no AS UNSIGNED) DESC')
            ->value('invoice_no');
        $nextNo = 1002;
        if ($maxExisting && preg_match('/(\d+)/', (string) $maxExisting, $mm)) {
            $nextNo = max(1001, (int) $mm[1]) + 1;
        }
        // Ensure unique invoice_no
        while (Invoice::withTrashed()->where('invoice_no', (string) $nextNo)->exists()) {
            $nextNo++;
        }

        // Find existing invoice for this shipment (one per shipment)
        // Use 1:1 relation via packing_list_id rather than shipment-level invoice
        $invoice = Invoice::query()->where('packing_list_id', $packingList->getKey())->latest()->first();
        $data = [
            'packing_list_id'=> $packingList->getKey(),
            'shipment_id'    => $shipment->getKey(),
            'user_id'        => $packingList->user_id ?? Auth::id(),
            'consignee_id'   => $indent?->consignee_id,
            'date'           => now()->toDateString(),
            'employee'       => $packingList->employee,
            'obl_ref'        => $shipment->booking_no,
            'no_of_cont'     => $noOfCont > 0 && $packingList->ship_in ? ($noOfCont . ' x ' . strtoupper($packingList->ship_in)) : (string) $noOfCont,
            'weight_mt'      => $weightMt,
            'description'    => $desc,
            'moisture_contain' => 'LESS THAN 12%',
            'rate_mt'        => $rate,
            'amount'         => $amount,
            'payment_terms'  => $indent?->payment,
            'advance'        => 0,
            'amount_due'     => $amount !== null ? $amount : null,
            'return_date'    => $shipment->booking_date,
            'cut_off_date'   => $packingList->date,
            'dept_est'       => $packingList->ship_date,
            'arrival'        => $packingList->arrival_date,
            'si_cut_off'     => null,
            'f_dest'         => $indent?->final_destination,
            'remarks'        => $indent?->remarks,
            'terms_conditions'=> $indent?->other_terms,
            'status'         => 'draft',
        ];

        try {
            if ($invoice) {
                Log::info('PackingListObserver: Updating existing invoice', ['invoice_id' => $invoice->getKey()]);
                $invoice->fill($data);
                $invoice->saveQuietly();
            } else {
                // Double-check uniqueness for race conditions
                if (Invoice::withTrashed()->where('invoice_no', (string) $nextNo)->exists()) {
                    do { $nextNo++; } while (Invoice::withTrashed()->where('invoice_no', (string) $nextNo)->exists());
                }
                $data['invoice_no'] = (string) $nextNo;
                $created = Invoice::create($data);
                Log::info('PackingListObserver: Created new invoice', ['invoice_id' => $created->getKey(), 'invoice_no' => $created->invoice_no]);
            }
        } catch (\Throwable $e) {
            Log::error('PackingListObserver: Failed to create/update invoice', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
                'packing_list_id' => $packingList->getKey(),
            ]);
        }
    }

    protected function syncCertificate(PackingList $packingList): void
    {
        try {
            $generator = app(CertificateGenerator::class);
            $coo = $generator->generateOrUpdateForPackingList($packingList);
            Log::info('PackingListObserver: COO synced', ['certificate_id' => $coo->getKey()]);
        } catch (\Throwable $e) {
            Log::error('PackingListObserver: Failed to create/update COO', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
                'packing_list_id' => $packingList->getKey(),
            ]);
        }
    }
}
