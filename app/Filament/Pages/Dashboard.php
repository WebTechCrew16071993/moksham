<?php

namespace App\Filament\Pages;

use Filament\Pages\Dashboard as BaseDashboard;

class Dashboard extends BaseDashboard
{
    public function getTitle(): string
    {
        return 'Dashboard';
    }

    public function getWidgets(): array
    {
        return [
            \App\Filament\Widgets\IndentsOverview::class,
            \App\Filament\Widgets\DocumentsOverview::class,
        ];
    }

    public function getColumns(): int|array
    {
        return [
            'default' => 12,
            'sm' => 12,
            'lg' => 12,
            '2xl' => 12,
        ];
    }
}
