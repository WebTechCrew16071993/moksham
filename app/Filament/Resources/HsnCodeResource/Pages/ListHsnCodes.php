<?php

namespace App\Filament\Resources\HsnCodeResource\Pages;

use App\Filament\Resources\HsnCodeResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListHsnCodes extends ListRecords
{
    protected static string $resource = HsnCodeResource::class;

    protected function authorizeAccess(): void
    {
        abort_unless(auth()->user()?->isAdmin(), 403);
    }

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
}
