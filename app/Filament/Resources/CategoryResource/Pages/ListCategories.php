<?php

namespace App\Filament\Resources\CategoryResource\Pages;

use App\Filament\Resources\CategoryResource;
use Filament\Resources\Pages\ListRecords;
use Filament\Actions;
use Illuminate\Support\Facades\Auth;

class ListCategories extends ListRecords
{
    protected static string $resource = CategoryResource::class;

    protected function authorizeAccess(): void
    {
        abort_unless(Auth::user()?->isAdmin(), 403);
    }

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
}
