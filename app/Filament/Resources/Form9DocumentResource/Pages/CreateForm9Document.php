<?php

namespace App\Filament\Resources\Form9DocumentResource\Pages;

use App\Filament\Resources\Form9DocumentResource;
use Filament\Resources\Pages\CreateRecord;

class CreateForm9Document extends CreateRecord
{
    protected static string $resource = Form9DocumentResource::class;

    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('index');
    }
}
