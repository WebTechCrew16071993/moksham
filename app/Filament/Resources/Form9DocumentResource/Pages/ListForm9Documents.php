<?php

namespace App\Filament\Resources\Form9DocumentResource\Pages;

use App\Filament\Resources\Form9DocumentResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListForm9Documents extends ListRecords
{
    protected static string $resource = Form9DocumentResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
}
