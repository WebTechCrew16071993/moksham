<?php

namespace App\Observers;

use App\Models\Invoice;
use App\Models\PackingList;
use App\Models\BlCorrection;
use App\Models\BlCorrectionItem;
use App\Models\CompanySetting;
use App\Services\CertificateGenerator;
use App\Services\ActivityLogger;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;

class PackingListObserver
{
    public function created(PackingList $packingList): void
    {
        // Invoice generation is handled via BL approval workflow now.
        // $this->syncInvoice($packingList);
        // $this->syncCertificate($packingList);
        $this->syncBl($packingList);

        ActivityLogger::log('packing_list.created', [
            'user_id' => $packingList->user_id ?? null,
            'subject_type' => PackingList::class,
            'subject_id' => $packingList->getKey(),
            'subject_label' => 'PL #' . $packingList->getKey(),
            'description' => 'Packing list created',
            'changes' => [
                'after' => $packingList->getAttributes(),
            ],
        ]);
    }

    public function updated(PackingList $packingList): void
    {
        // Invoice generation is handled via BL approval workflow now.
        // $this->syncInvoice($packingList);
        // $this->syncCertificate($packingList);
        $this->syncBl($packingList);

        ActivityLogger::log('packing_list.updated', [
            'user_id' => $packingList->user_id ?? null,
            'subject_type' => PackingList::class,
            'subject_id' => $packingList->getKey(),
            'subject_label' => 'PL #' . $packingList->getKey(),
            'description' => 'Packing list updated',
            'changes' => [
                'after' => $packingList->getAttributes(),
            ],
        ]);
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
                // Use normal save so Eloquent events fire and InvoiceObserver runs
                $invoice->save();
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

    /**
     * Ensure a BL draft exists and stays in sync with the Packing List.
     */
    protected function syncBl(PackingList $packingList): void
    {

        try {
            $packingList->loadMissing(['shipment.indent', 'items']);
            $shipment = $packingList->shipment;
            $indent = $shipment?->indent;

            $setting = CompanySetting::query()->first();
            $shipperDetails = $setting ? trim(implode("\n", array_filter([
                $setting->company_name,
                $setting->company_address,
                trim(($setting->company_city.' '.$setting->company_state.' '.$setting->company_zip)),
                $setting->company_country,
            ]))) : null;

            $consigneeBlock = $indent ? trim(implode("\n", array_filter([
                $indent->consignee_name,
                $indent->consignee_address,
                trim(($indent->consignee_city.' '.$indent->consignee_state.' '.$indent->consignee_zip)),
                $indent->consignee_country,
            ]))) : null;

            $kg = (float) ($packingList->total_weight_kg ?? 0);
            if ($kg <= 0) {
                $kg = (float) $packingList->items->sum('weight_kg');
            }
            $mts = round($kg / 1000, 3);

            $bl = BlCorrection::query()
                ->where('packing_list_id', $packingList->getKey())
                ->latest('id')
                ->first();
            if (!$bl) {
                $bl = new BlCorrection();
                $bl->shipment_id = $packingList->shipment_id;
                $bl->packing_list_id = $packingList->getKey();
                $bl->user_id = $packingList->user_id ?? Auth::id();
                $bl->booking_no = $shipment?->booking_no;
                $bl->bl_date = now()->toDateString();
                $bl->status = 'draft';
            }

            $bl->fill([
                'shipper_details' => $shipperDetails,
                'shipper_phone' => $setting?->company_phone,
                'shipper_email' => $setting?->company_email,
                'consignee_details' => $consigneeBlock,
                'consignee_iec' => $indent?->consignee_iec,
                'consignee_gstin' => $indent?->consignee_gstin,
                'consignee_pan' => $indent?->consignee_pan,
                'consignee_email' => $indent?->consignee_email,
                'notify_party_details' => $consigneeBlock,
                'notify_party_iec' => $indent?->consignee_iec,
                'notify_party_gstin' => $indent?->consignee_gstin,
                'notify_party_pan' => $indent?->consignee_pan,
                'notify_party_email' => $indent?->consignee_email,
                'port_of_loading' => (string) $packingList->origin,
                'origin' => (string) $packingList->origin,
                'destination' => (string) $packingList->destination,
                'net_weight_kgs' => $kg,
                'net_weight_mts' => $mts,
                'packaging_type' => 'BALE, COMPRESSED',
                'ship_in' => (string) $packingList->ship_in,
                'no_of_containers' => $packingList->items->count(),
                'container_type' => (string) $packingList->ship_in,
                'total_bales' => (int) ($packingList->total_bales ?? $packingList->items->sum('no_of_bales')),
                'hs_code' => $indent?->hsn_code,
                'commodity_description' => $indent && $indent->hsn_description && $indent->hsn_code
                    ? (trim($indent->hsn_description) . ' HS CODE ' . trim($indent->hsn_code))
                    : null,
            ]);
            $bl->saveQuietly();

            // Rebuild BL items
            $bl->items()->delete();
            foreach ($packingList->items as $row) {
                BlCorrectionItem::create([
                    'bl_correction_id' => $bl->getKey(),
                    'container_no' => (string) $row->container_no,
                    'seal_no' => (string) $row->seal_no,
                    'commodity' => (string) ($row->description ?? $bl->commodity_description),
                    'hs_code' => (string) ($bl->hs_code ?? $indent?->hsn_code),
                    'no_of_bales' => (int) $row->no_of_bales,
                    'weight_kgs' => (float) $row->weight_kg,
                ]);
            }

            Log::info('PackingListObserver: BL synced', [
                'packing_list_id' => $packingList->getKey(),
                'bl_id' => $bl->getKey(),
            ]);
        } catch (\Throwable $e) {
            Log::error('PackingListObserver: BL sync failed', [
                'packing_list_id' => $packingList->getKey(),
                'error' => $e->getMessage(),
            ]);
        }
    }
}
