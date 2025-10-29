<?php

namespace App\Filament\Resources\ImportCompanyResource\Pages;

use App\Filament\Resources\ImportCompanyResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListImportCompanies extends ListRecords
{
    protected static string $resource = ImportCompanyResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make()->visible(fn (): bool => auth()->user()?->isAdmin() ?? false),
        ];
    }
}
