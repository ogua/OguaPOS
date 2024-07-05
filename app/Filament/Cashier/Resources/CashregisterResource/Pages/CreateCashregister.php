<?php

namespace App\Filament\Cashier\Resources\CashregisterResource\Pages;

use App\Filament\Cashier\Resources\CashregisterResource;
use Filament\Actions;
use Filament\Resources\Pages\CreateRecord;

class CreateCashregister extends CreateRecord
{
    protected static string $resource = CashregisterResource::class;

    protected function getFormActions(): array
    {
        return [
             $this->getCreateFormAction(),
        ];
    }

    protected function getRedirectUrl(): string
    {
        return "/pos";
    }
}
