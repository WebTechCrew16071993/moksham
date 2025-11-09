<?php

namespace App\Filament\Resources\BlCorrectionResource\Pages;

use App\Filament\Resources\BlCorrectionResource;
use Filament\Resources\Pages\CreateRecord;

class CreateBlCorrection extends CreateRecord
{
    protected static string $resource = BlCorrectionResource::class;

    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('index');
    }
}
