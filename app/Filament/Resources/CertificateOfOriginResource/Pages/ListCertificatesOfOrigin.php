<?php

namespace App\Filament\Resources\CertificateOfOriginResource\Pages;

use App\Filament\Resources\CertificateOfOriginResource;
use Filament\Resources\Pages\ListRecords;
use Filament\Actions;

class ListCertificatesOfOrigin extends ListRecords
{
    protected static string $resource = CertificateOfOriginResource::class;

    protected function getHeaderWidgets(): array
    {
        return [
            \App\Filament\Widgets\CertificatesOverview::class,
        ];
    }

    public function getHeaderWidgetsColumns(): int
    {
        // Give the single stats widget full width; it will render 5 cards in one row internally
        return 1;
    }

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
}
