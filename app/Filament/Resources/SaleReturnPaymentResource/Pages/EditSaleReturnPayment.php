<?php

namespace App\Filament\Resources\SaleReturnPaymentResource\Pages;

use App\Filament\Resources\SaleReturnPaymentResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditSaleReturnPayment extends EditRecord
{
    protected static string $resource = SaleReturnPaymentResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }
}
