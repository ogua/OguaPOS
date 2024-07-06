<?php

namespace App\Filament\Cashier\Resources\CashregisterResource\Pages;

use App\Filament\Cashier\Resources\CashregisterResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListCashregisters extends ListRecords
{
    protected static string $resource = CashregisterResource::class;

    protected function getHeaderActions(): array
    {
        return [
           // Actions\CreateAction::make(),
        ];
    }
}
