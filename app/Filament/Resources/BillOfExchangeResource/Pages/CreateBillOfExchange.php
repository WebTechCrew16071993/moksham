<?php

namespace App\Filament\Resources\BillOfExchangeResource\Pages;

use App\Filament\Resources\BillOfExchangeResource;
use Filament\Resources\Pages\CreateRecord;

class CreateBillOfExchange extends CreateRecord
{
    protected static string $resource = BillOfExchangeResource::class;

    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('index');
    }
}
