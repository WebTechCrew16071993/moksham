<?php

namespace App\Filament\Resources\Form6DocumentResource\Pages;

use App\Filament\Resources\Form6DocumentResource;
use Filament\Resources\Pages\CreateRecord;

class CreateForm6Document extends CreateRecord
{
    protected static string $resource = Form6DocumentResource::class;

    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('index');
    }
}
