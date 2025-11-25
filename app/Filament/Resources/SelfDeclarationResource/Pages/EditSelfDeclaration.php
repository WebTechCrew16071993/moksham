<?php

namespace App\Filament\Resources\SelfDeclarationResource\Pages;

use App\Filament\Resources\SelfDeclarationResource;
use Filament\Resources\Pages\EditRecord;

class EditSelfDeclaration extends EditRecord
{
    protected static string $resource = SelfDeclarationResource::class;

    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('index');
    }
}
