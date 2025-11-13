<?php

namespace App\Filament\Resources\Form9DocumentResource\Pages;

use App\Filament\Resources\Form9DocumentResource;
use Filament\Resources\Pages\EditRecord;

class EditForm9Document extends EditRecord
{
    protected static string $resource = Form9DocumentResource::class;

    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('index');
    }
}
