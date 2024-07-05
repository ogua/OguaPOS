<?php

namespace App\Filament\Resources\CashregisterResource\Pages;

use App\Filament\Resources\CashregisterResource;
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
