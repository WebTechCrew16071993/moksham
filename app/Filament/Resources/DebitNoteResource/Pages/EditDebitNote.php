<?php

namespace App\Filament\Resources\DebitNoteResource\Pages;

use App\Filament\Resources\DebitNoteResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditDebitNote extends EditRecord
{
    protected static string $resource = DebitNoteResource::class;

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
