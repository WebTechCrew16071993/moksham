<?php

namespace App\Filament\Resources\PackingListResource\Pages;

use App\Filament\Resources\PackingListResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;
use App\Models\PackingList;
use App\Services\InvoiceGenerator;

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
            Actions\DeleteAction::make(),
        ];
    }

    protected function mutateFormDataBeforeSave(array $data): array
    {
        // Totals
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

        return $data;
    }

    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('index');
    }

    protected function afterSave(): void
    {
        $record = $this->record;
        $generator = app(InvoiceGenerator::class);
        $generator->generateOrUpdateForPackingList($record);
    }
}
