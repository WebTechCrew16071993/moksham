<?php

namespace App\Filament\Resources\ImportCompanyResource\Pages;

use App\Filament\Resources\ImportCompanyResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;
use Illuminate\Support\Facades\Auth;

class EditImportCompany extends EditRecord
{
    protected static string $resource = ImportCompanyResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make()->visible(fn (): bool => Auth::user()?->isAdmin() ?? false),
        ];
    }

    protected function authorizeAccess(): void
    {
        abort_unless(Auth::user()?->isAdmin(), 403);
    }

    protected function getRedirectUrl(): string
    {
        return static::getResource()::getUrl('index');
    }
}
