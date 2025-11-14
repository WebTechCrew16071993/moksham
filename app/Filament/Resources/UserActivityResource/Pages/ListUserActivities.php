<?php

namespace App\Filament\Resources\UserActivityResource\Pages;

use App\Filament\Resources\UserActivityResource;
use Filament\Resources\Pages\ListRecords;
use Illuminate\Support\Facades\Auth;

class ListUserActivities extends ListRecords
{
    protected static string $resource = UserActivityResource::class;

    protected function getHeaderActions(): array
    {
        return [];
    }

    protected function authorizeAccess(): void
    {
        abort_unless(Auth::user()?->isAdmin(), 403);
    }
}
