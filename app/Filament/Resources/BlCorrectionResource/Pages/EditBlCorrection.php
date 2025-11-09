<?php

namespace App\Filament\Resources\BlCorrectionResource\Pages;

use App\Filament\Resources\BlCorrectionResource;
use App\Models\PackingList;
use App\Services\InvoiceGenerator;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\EditRecord;

class EditBlCorrection extends EditRecord
{
    protected static string $resource = BlCorrectionResource::class;

    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('index');
    }

    protected function afterSave(): void
    {
        $record = $this->record; // BlCorrection model

        // If BL is approved/finalized, sync invoice using Packing List
        if (in_array($record->status, ['approved', 'finalized'])) {
            try {
                $record->loadMissing('packingList');
                $pl = $record->packingList;
                if (!$pl) {
                    $pl = PackingList::where('shipment_id', $record->shipment_id)->latest('id')->first();
                }
                if ($pl) {
                    $generator = app(InvoiceGenerator::class);
                    $generator->generateOrUpdateForPackingList($pl);
                    Notification::make()
                        ->title('Invoice updated')
                        ->success()
                        ->send();
                } else {
                    Notification::make()
                        ->title('Packing List not found for invoice update')
                        ->warning()
                        ->send();
                }
            } catch (\Throwable $e) {
                Notification::make()
                    ->title('Failed to update invoice')
                    ->body($e->getMessage())
                    ->danger()
                    ->send();
            }
        }
    }
}
