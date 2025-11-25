<?php

namespace App\Filament\Resources\DocumentaryCollectionLetterResource\Pages;

use App\Filament\Resources\DocumentaryCollectionLetterResource;
use Filament\Resources\Pages\CreateRecord;

class CreateDocumentaryCollectionLetter extends CreateRecord
{
    protected static string $resource = DocumentaryCollectionLetterResource::class;

    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('index');
    }
}
