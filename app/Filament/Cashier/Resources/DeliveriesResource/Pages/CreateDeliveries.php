<?php

namespace App\Filament\Cashier\Resources\DeliveriesResource\Pages;

use App\Filament\Cashier\Resources\DeliveriesResource;
use Filament\Actions;
use Filament\Resources\Pages\CreateRecord;

class CreateDeliveries extends CreateRecord
{
    protected static string $resource = DeliveriesResource::class;

    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('index');
    }
}
