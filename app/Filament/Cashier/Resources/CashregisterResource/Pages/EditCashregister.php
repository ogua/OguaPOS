<?php

namespace App\Filament\Cashier\Resources\CashregisterResource\Pages;

use App\Filament\Cashier\Resources\CashregisterResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditCashregister extends EditRecord
{
    protected static string $resource = CashregisterResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }
}
