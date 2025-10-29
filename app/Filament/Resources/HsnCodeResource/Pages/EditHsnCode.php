<?php

namespace App\Filament\Resources\HsnCodeResource\Pages;

use App\Filament\Resources\HsnCodeResource;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\EditRecord;
use Illuminate\Support\Facades\Auth;

class EditHsnCode extends EditRecord
{
    protected static string $resource = HsnCodeResource::class;

    protected function authorizeAccess(): void
    {
        abort_unless(Auth::user()?->isAdmin(), 403);
    }

    protected function getSavedNotification(): ?Notification
    {
        return Notification::make()
            ->title('HSN Code updated successfully')
            ->success();
    }

    protected function getRedirectUrl(): string
    {
        return static::getResource()::getUrl('index');
    }
}
