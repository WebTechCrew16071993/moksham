<?php

namespace App\Filament\Resources\CreditDebitNoteResource\Pages;

use App\Filament\Resources\CreditDebitNoteResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditCreditDebitNote extends EditRecord
{
    protected static string $resource = CreditDebitNoteResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\Action::make('pdf')
                ->label('PDF')
                ->icon('heroicon-o-arrow-down-tray')
                ->url(fn () => route('cdn.pdf', ['note' => $this->record->getKey(), 'download' => 0]))
                ->openUrlInNewTab(),
            Actions\DeleteAction::make(),
        ];
    }

    protected function getRedirectUrl(): string
    {
        return static::getResource()::getUrl('index');
    }
}
