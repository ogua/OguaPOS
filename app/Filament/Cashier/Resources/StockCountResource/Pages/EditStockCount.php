<?php

namespace App\Filament\Cashier\Resources\StockCountResource\Pages;

use App\Filament\Cashier\Resources\StockCountResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditStockCount extends EditRecord
{
    protected static string $resource = StockCountResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }
}
