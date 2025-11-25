<?php

namespace App\Filament\Resources\BillOfExchangeResource\Pages;

use App\Filament\Resources\BillOfExchangeResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditBillOfExchange extends EditRecord
{
    protected static string $resource = BillOfExchangeResource::class;

    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('index');
    }

   
}
