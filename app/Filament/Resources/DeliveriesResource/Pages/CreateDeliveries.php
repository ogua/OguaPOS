<?php

namespace App\Filament\Resources\DeliveriesResource\Pages;

use App\Filament\Resources\DeliveriesResource;
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
