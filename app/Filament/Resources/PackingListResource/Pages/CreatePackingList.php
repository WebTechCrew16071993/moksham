<?php

namespace App\Filament\Resources\PackingListResource\Pages;

use App\Filament\Resources\PackingListResource;
use App\Models\PackingList;
use App\Models\BlCorrection;
use App\Models\BlCorrectionItem;
use App\Models\CompanySetting;
use App\Services\InvoiceGenerator;
use App\Services\CertificateGenerator;
use Illuminate\Support\Facades\Log;
use Filament\Resources\Pages\CreateRecord;

class CreatePackingList extends CreateRecord
{
    protected static string $resource = PackingListResource::class;

    protected function mutateFormDataBeforeCreate(array $data): array
    {
        // Compute totals from repeater items
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

        // ✅ Only auto-fill container summary if user left it blank
        if (empty($data['container_no_summary'])) {
            $count = is_array($items) ? count($items) : 0;
            $shipIn = isset($data['ship_in']) ? strtoupper((string)$data['ship_in']) : null;
            if ($count > 0 && $shipIn) {
                $data['container_no_summary'] = "{$count} x {$shipIn}";
            }
        }

        return $data;
    }

    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('index');
    }

    protected function getFormActions(): array
    {
        // Keep only Create and Cancel actions
        return [
            $this->getCreateFormAction()->label('Create'),
            $this->getCancelFormAction(),
        ];
    }

    protected function afterCreate(): void
    {
        /** @var PackingList $record */
        $record = $this->record;
        try {
            $shipment = $record->shipment()->with('indent')->first();
            $indent = $shipment?->indent;

            $kg = (float) $record->total_weight_kg;
            $mts = round($kg / 1000, 3);
            $items = $record->items()->get();

            // Build shipper and consignee details
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

            $bl = BlCorrection::create([
                'shipment_id' => $record->shipment_id,
                'packing_list_id' => $record->getKey(),
                'user_id' => $record->user_id,
                'booking_no' => $shipment?->booking_no,
                'bl_date' => now(),
                // shipper
                'shipper_details' => $shipperDetails,
                'shipper_phone' => $shipperPhone,
                'shipper_email' => $shipperEmail,
                // consignee/notify party from indent
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
                'no_of_containers' => max(0, $items->count()),
                'container_type' => (string) $record->ship_in,
                'document_type' => 'OBL',
                'total_bales' => (int) $record->total_bales,
                'hs_code' => $indent?->hsn_code,
                'commodity_description' => $indent && $indent->hsn_description && $indent->hsn_code
                    ? (trim($indent->hsn_description) . ' HS CODE ' . trim($indent->hsn_code))
                    : null,
                'status' => 'draft',
            ]);

            foreach ($items as $row) {
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

            Log::info('CreatePackingList: BL draft auto-created', [
                'packing_list_id' => $record->getKey(),
                'bl_id' => $bl->getKey(),
            ]);
        } catch (\Throwable $e) {
            Log::error('CreatePackingList: BL draft creation failed', [
                'packing_list_id' => $record->getKey(),
                'error' => $e->getMessage(),
            ]);
        }
    }

    protected function hasCreateAnotherAction(): bool
    {
        // Hide the separate "Create & create another" action
        return false;
    }
}
