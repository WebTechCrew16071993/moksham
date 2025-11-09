<?php

namespace App\Filament\Resources\Form6DocumentResource\Pages;

use App\Filament\Resources\Form6DocumentResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListForm6Documents extends ListRecords
{
    protected static string $resource = Form6DocumentResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
}
