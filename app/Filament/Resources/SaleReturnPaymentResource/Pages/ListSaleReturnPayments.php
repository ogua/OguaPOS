<?php

namespace App\Filament\Resources\SaleReturnPaymentResource\Pages;

use App\Filament\Resources\SaleReturnPaymentResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListSaleReturnPayments extends ListRecords
{
    protected static string $resource = SaleReturnPaymentResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
}
