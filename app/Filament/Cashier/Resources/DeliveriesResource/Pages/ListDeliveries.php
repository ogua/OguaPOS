<?php

namespace App\Filament\Cashier\Resources\DeliveriesResource\Pages;

use App\Filament\Cashier\Resources\DeliveriesResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListDeliveries extends ListRecords
{
    protected static string $resource = DeliveriesResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
}
