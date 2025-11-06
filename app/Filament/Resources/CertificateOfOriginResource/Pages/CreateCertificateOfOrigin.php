<?php

namespace App\Filament\Resources\CertificateOfOriginResource\Pages;

use App\Filament\Resources\CertificateOfOriginResource;
use Filament\Resources\Pages\CreateRecord;

class CreateCertificateOfOrigin extends CreateRecord
{
    protected static string $resource = CertificateOfOriginResource::class;

    protected function getRedirectUrl(): string
    {
        return static::getResource()::getUrl('index');
    }

    protected function hasCreateAnotherAction(): bool
    {
        return false;
    }
}
