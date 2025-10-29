<?php

namespace App\Filament\Resources\UserResource\Pages;

use App\Filament\Resources\UserResource;
use Filament\Resources\Pages\CreateRecord;

class CreateUser extends CreateRecord
{
    protected static string $resource = UserResource::class;

    protected function authorizeAccess(): void
    {
        abort_unless(auth()->user()?->isAdmin(), 403);
    }

    protected function getRedirectUrl(): string
    {
        // After create, go back to the Users listing page
        return static::getResource()::getUrl('index');
    }
}
