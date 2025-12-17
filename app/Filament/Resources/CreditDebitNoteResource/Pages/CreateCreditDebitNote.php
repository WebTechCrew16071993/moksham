<?php

namespace App\Filament\Resources\CreditDebitNoteResource\Pages;

use App\Filament\Resources\CreditDebitNoteResource;
use Filament\Resources\Pages\CreateRecord;

class CreateCreditDebitNote extends CreateRecord
{
    protected static string $resource = CreditDebitNoteResource::class;

    protected function getRedirectUrl(): string
    {
        return static::getResource()::getUrl('index');
    }
}
