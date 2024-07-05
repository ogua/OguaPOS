<?php

namespace App\Filament\Resources\CashregisterResource\Pages;

use App\Filament\Resources\CashregisterResource;
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
}
