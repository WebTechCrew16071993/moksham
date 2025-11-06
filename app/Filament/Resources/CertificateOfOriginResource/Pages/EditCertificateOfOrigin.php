<?php

namespace App\Filament\Resources\CertificateOfOriginResource\Pages;

use App\Filament\Resources\CertificateOfOriginResource;
use Filament\Resources\Pages\EditRecord;

class EditCertificateOfOrigin extends EditRecord
{
    protected static string $resource = CertificateOfOriginResource::class;

    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('index');
    }
}
