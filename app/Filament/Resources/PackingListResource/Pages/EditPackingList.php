<?php

namespace App\Filament\Resources\PackingListResource\Pages;

use App\Filament\Resources\PackingListResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;
use App\Models\PackingList;
use App\Models\BlCorrection;
use App\Models\BlCorrectionItem;
use App\Models\CompanySetting;
use App\Services\InvoiceGenerator;
use App\Services\CertificateGenerator;
use Illuminate\Support\Facades\Log;

class EditPackingList extends EditRecord
{
    protected static string $resource = PackingListResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\Action::make('generateInvoice')
                ->label('Generate Invoice')
                ->icon('heroicon-o-document-currency-dollar')
                ->visible(fn() => !$this->record?->shipment?->invoices()->exists()
                    && optional($this->record?->latestBl())->status === 'finalized')
                ->requiresConfirmation()
                ->action(function () {
                    /** @var PackingList $packingList */
                    $packingList = $this->record;
                    /** @var InvoiceGenerator $generator */
                    $generator = app(InvoiceGenerator::class);
                    $invoice = $generator->generateOrUpdateForPackingList($packingList);
                    return redirect()->route('invoices.pdf', ['invoice' => $invoice->getKey(), 'download' => 0]);
                }),
            Actions\Action::make('pdf')
                ->label('PDF')
                ->icon('heroicon-o-document-text')
                ->url(fn() => route('packing-lists.generate', ['packingList' => $this->record->getKey(), 'download' => 0]))
                ->openUrlInNewTab(),
            Actions\Action::make('generateCoo')
                ->label('Generate COO')
                ->icon('heroicon-o-document-text')
                ->visible(fn() => !$this->record?->shipment?->certificatesOfOrigin()->exists())
                ->requiresConfirmation()
                ->action(function () {
                    /** @var PackingList $packingList */
                    $packingList = $this->record;
                    /** @var CertificateGenerator $generator */
                    $generator = app(CertificateGenerator::class);
                    $coo = $generator->generateOrUpdateForPackingList($packingList);
                    return redirect()->route('certificates.pdf', ['certificate' => $coo->getKey(), 'download' => 0]);
                }),
            Actions\DeleteAction::make(),
        ];
    }

    protected function mutateFormDataBeforeSave(array $data): array
    {
        // Totals
        Log::info('EditPackingList: mutateFormDataBeforeSave:start', [
            'packing_list_id' => $this->record?->getKey(),
            'shipment_id' => $this->record?->shipment_id ?? ($data['shipment_id'] ?? null),
        ]);
        $items = $data['items'] ?? [];
        $totalBales = 0;
        $totalLbs = 0.0;

        foreach ($items as $row) {
            $totalBales += (int)($row['no_of_bales'] ?? 0);
            $totalLbs += (float)($row['weight_lbs'] ?? 0);
        }

        $data['total_bales'] = $totalBales;
        $data['total_weight_lbs'] = $totalLbs;
        $data['total_weight_kg'] = round($totalLbs * 0.453592, 3);

        // ✅ Only auto-fill container summary if user left it empty
        if (empty($data['container_no_summary'])) {
            $count = is_array($items) ? count($items) : 0;
            $shipIn = isset($data['ship_in'])
                ? strtoupper((string)$data['ship_in'])
                : ($this->record?->ship_in ? strtoupper((string)$this->record->ship_in) : null);

            if ($count > 0 && $shipIn) {
                $data['container_no_summary'] = "{$count} x {$shipIn}";
            }
        }

        Log::info('EditPackingList: mutateFormDataBeforeSave:computed', [
            'packing_list_id' => $this->record?->getKey(),
            'items_count' => is_array($items) ? count($items) : 0,
            'total_bales' => $totalBales,
            'total_lbs' => $totalLbs,
            'total_kg' => $data['total_weight_kg'],
            'container_no_summary' => $data['container_no_summary'] ?? null,
        ]);

        return $data;
    }

    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('index');
    }

    protected function afterSave(): void
    {
        $record = $this->record;
        Log::info('EditPackingList: afterSave:start', [
            'packing_list_id' => $record->getKey(),
            'shipment_id' => $record->shipment_id,
            'items_count' => $record->items()->count(),
        ]);

        // Invoice generation moved to BL workflow (after Approve/Finalize)
        // Keeping the code commented as requested
        // try {
        //     $generator = app(InvoiceGenerator::class);
        //     $invoice = $generator->generateOrUpdateForPackingList($record);
        //     Log::info('EditPackingList: afterSave:invoiceSynced', [
        //         'invoice_id' => $invoice?->getKey(),
        //         'invoice_no' => $invoice?->invoice_no,
        //     ]);
        // } catch (\Throwable $e) {
        //     Log::error('EditPackingList: afterSave:invoiceError', [
        //         'error' => $e->getMessage(),
        //         'trace' => $e->getTraceAsString(),
        //     ]);
        // }

        // Update related BL (shipper/consignee details and container lines)
        try {
            $shipment = $record->shipment()->with('indent')->first();
            $indent = $shipment?->indent;

            $setting = CompanySetting::query()->first();
            $shipperDetails = $setting ? trim(implode("\n", array_filter([
                $setting->company_name,
                $setting->company_address,
                trim(($setting->company_city.' '.$setting->company_state.' '.$setting->company_zip)),
                $setting->company_country,
            ]))) : null;
            $shipperPhone = $setting->company_phone ?? null;
            $shipperEmail = $setting->company_email ?? null;

            $consigneeBlock = $indent ? trim(implode("\n", array_filter([
                $indent->consignee_name,
                $indent->consignee_address,
                trim(($indent->consignee_city.' '.$indent->consignee_state.' '.$indent->consignee_zip)),
                $indent->consignee_country,
            ]))) : null;

            $kg = (float) $record->total_weight_kg;
            $mts = round($kg / 1000, 3);

            $bl = $record->latestBl();
            if (!$bl) {
                $bl = new BlCorrection();
                $bl->shipment_id = $record->shipment_id;
                $bl->packing_list_id = $record->getKey();
                $bl->user_id = $record->user_id;
                $bl->booking_no = $shipment?->booking_no;
                $bl->bl_date = now()->toDateString();
                $bl->status = 'draft';
            }

            $bl->fill([
                'shipper_details' => $shipperDetails,
                'shipper_phone' => $shipperPhone,
                'shipper_email' => $shipperEmail,
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
                'port_of_loading' => (string) $record->origin,
                'origin' => (string) $record->origin,
                'destination' => (string) $record->destination,
                'net_weight_kgs' => $kg,
                'net_weight_mts' => $mts,
                'cargo_value' => 0,
                'currency' => 'USD',
                'packaging_type' => 'BALE, COMPRESSED',
                'ship_in' => (string) $record->ship_in,
                'no_of_containers' => $record->items()->count(),
                'container_type' => (string) $record->ship_in,
                'total_bales' => (int) $record->total_bales,
                'hs_code' => $indent?->hsn_code,
                'commodity_description' => $indent && $indent->hsn_description && $indent->hsn_code
                    ? (trim($indent->hsn_description) . ' HS CODE ' . trim($indent->hsn_code))
                    : null,
            ]);
            $bl->save();

            // Sync items: recreate from packing list items
            $bl->items()->delete();
            foreach ($record->items as $row) {
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

            Log::info('EditPackingList: afterSave:blSynced', [
                'bl_id' => $bl->getKey(),
                'status' => $bl->status,
            ]);
        } catch (\Throwable $e) {
            Log::error('EditPackingList: afterSave:blSyncError', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);
        }
    }
}
