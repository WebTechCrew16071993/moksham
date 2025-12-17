<?php

namespace App\Filament\Resources\InvoiceResource\Pages;

use App\Filament\Resources\InvoiceResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;
use App\Models\BlCorrection;
use Illuminate\Validation\ValidationException;

class EditInvoice extends EditRecord
{
    protected static string $resource = InvoiceResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\Action::make('pdf')
                ->label('PDF')
                ->icon('heroicon-o-arrow-down-tray')
                ->url(fn () => route('invoices.pdf', ['invoice' => $this->record->getKey(), 'download' => 0]))
                ->openUrlInNewTab(),
            Actions\DeleteAction::make(),
        ];
    }

    protected function getRedirectUrl(): string
    {
        return static::getResource()::getUrl('index');
    }

    protected function mutateFormDataBeforeSave(array $data): array
    {
        $shipmentId = $data['shipment_id'] ?? null;
        $blId = $data['bl_correction_id'] ?? null;
        if ($shipmentId && $blId) {
            $bl = BlCorrection::find($blId);
            if ($bl && (int) $bl->shipment_id !== (int) $shipmentId) {
                throw ValidationException::withMessages([
                    'bl_correction_id' => 'Selected BL does not belong to the chosen Shipment.',
                ]);
            }
        }
        return $data;
    }
}
