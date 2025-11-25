<?php

namespace App\Filament\Resources\DocumentaryCollectionLetterResource\Pages;

use App\Filament\Resources\DocumentaryCollectionLetterResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditDocumentaryCollectionLetter extends EditRecord
{
    protected static string $resource = DocumentaryCollectionLetterResource::class;

    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('index');
    }

    // protected function getHeaderActions(): array
    // {
    //     return [
    //         Actions\DeleteAction::make(),
    //         Actions\ForceDeleteAction::make(),
    //         Actions\RestoreAction::make(),
    //     ];
    // }
}
