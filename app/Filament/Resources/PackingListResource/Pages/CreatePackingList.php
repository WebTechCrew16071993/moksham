<?php

namespace App\Filament\Resources\PackingListResource\Pages;

use App\Filament\Resources\PackingListResource;
use App\Models\PackingList;
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
            /** @var InvoiceGenerator $generator */
            $generator = app(InvoiceGenerator::class);
            $invoice = $generator->generateOrUpdateForPackingList($record);
            Log::info('CreatePackingList: invoice generated after create', [
                'packing_list_id' => $record->getKey(),
                'invoice_id' => $invoice->getKey(),
            ]);
        } catch (\Throwable $e) {
            Log::error('CreatePackingList: invoice generation failed', [
                'packing_list_id' => $record->getKey(),
                'error' => $e->getMessage(),
            ]);
        }

        try {
            /** @var CertificateGenerator $cooGen */
            $cooGen = app(CertificateGenerator::class);
            $coo = $cooGen->generateOrUpdateForPackingList($record);
            Log::info('CreatePackingList: COO generated after create', [
                'packing_list_id' => $record->getKey(),
                'certificate_id' => $coo->getKey(),
                'document_no' => $coo->document_no,
            ]);
        } catch (\Throwable $e) {
            Log::error('CreatePackingList: COO generation failed', [
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
