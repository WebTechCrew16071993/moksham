<?php

namespace App\Filament\Resources\DebitNoteResource\Pages;

use App\Filament\Resources\DebitNoteResource;
use Filament\Resources\Pages\CreateRecord;

class CreateDebitNote extends CreateRecord
{
    protected static string $resource = DebitNoteResource::class;

    protected function mutateFormDataBeforeCreate(array $data): array
    {
        $data['type'] = 'debit';
        return $data;
    }

    protected function getRedirectUrl(): string
    {
        return static::getResource()::getUrl('index');
    }
}
