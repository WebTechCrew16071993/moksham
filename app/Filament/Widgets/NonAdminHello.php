<?php

namespace App\Filament\Widgets;

use Filament\Widgets\Widget;
use Illuminate\Support\Facades\Auth;

class NonAdminHello extends Widget
{
    protected static string $view = 'filament.widgets.non-admin-hello';

    public static function canView(): bool
    {
        if (!request()->routeIs('filament.admin.pages.dashboard')) {
            return false;
        }
        $user = Auth::user();
        return $user && method_exists($user, 'isAdmin') && !$user->isAdmin();
    }
}
