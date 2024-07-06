<?php

namespace App\Filament\Cashier\Resources\DeliveriesResource\Pages;

use App\Filament\Cashier\Resources\DeliveriesResource;
use Filament\Actions;
use Filament\Resources\Pages\ViewRecord;

class ViewDeliveries extends ViewRecord
{
    protected static string $resource = DeliveriesResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\EditAction::make(),
        ];
    }
}
