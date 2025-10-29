<?php

namespace App\Filament\Resources\IndentResource\Pages;

use App\Filament\Resources\IndentResource;
use Filament\Resources\Pages\ListRecords;
use Filament\Actions;

class ListIndents extends ListRecords
{
    protected static string $resource = IndentResource::class;

    protected function getHeaderWidgets(): array
    {
        return [
            \App\Filament\Widgets\IndentsOverview::class,
        ];
    }

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
}
