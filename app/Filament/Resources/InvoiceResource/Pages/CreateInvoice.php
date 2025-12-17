<?php

namespace App\Filament\Resources\InvoiceResource\Pages;

use App\Filament\Resources\InvoiceResource;
use App\Models\BlCorrection;
use Filament\Resources\Pages\CreateRecord;
use Illuminate\Validation\ValidationException;

class CreateInvoice extends CreateRecord
{
    protected static string $resource = InvoiceResource::class;

    protected function getRedirectUrl(): string
    {
        return static::getResource()::getUrl('index');
    }

    protected function getFormActions(): array
    {
        // Keep only Create and Cancel actions
        return [
            $this->getCreateFormAction()->label('Create'),
            $this->getCancelFormAction(),
        ];
    }

    protected function hasCreateAnotherAction(): bool
    {
        // Hide the separate "Create & create another" action
        return false;
    }

    protected function mutateFormDataBeforeCreate(array $data): array
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
