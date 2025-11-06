<?php

namespace App\Filament\Resources\PackingListResource\Pages;

use App\Filament\Resources\PackingListResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;
use App\Models\PackingList;
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
                ->visible(fn() => !$this->record?->shipment?->invoices()->exists())
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

        try {
            $generator = app(InvoiceGenerator::class);
            $invoice = $generator->generateOrUpdateForPackingList($record);
            Log::info('EditPackingList: afterSave:invoiceSynced', [
                'invoice_id' => $invoice?->getKey(),
                'invoice_no' => $invoice?->invoice_no,
            ]);
        } catch (\Throwable $e) {
            Log::error('EditPackingList: afterSave:invoiceError', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);
        }

        try {
            // Ensure COO exists/updates as well
            $cooGen = app(CertificateGenerator::class);
            $coo = $cooGen->generateOrUpdateForPackingList($record);
            Log::info('EditPackingList: afterSave:cooSynced', [
                'certificate_id' => $coo?->getKey(),
                'document_no' => $coo?->document_no,
            ]);
        } catch (\Throwable $e) {
            Log::error('EditPackingList: afterSave:cooError', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);
        }
    }
}
