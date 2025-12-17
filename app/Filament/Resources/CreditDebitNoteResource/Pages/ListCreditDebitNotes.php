<?php

namespace App\Filament\Resources\CreditDebitNoteResource\Pages;

use App\Filament\Resources\CreditDebitNoteResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListCreditDebitNotes extends ListRecords
{
    protected static string $resource = CreditDebitNoteResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
}
