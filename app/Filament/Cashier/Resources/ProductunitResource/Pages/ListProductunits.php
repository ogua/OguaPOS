<?php

namespace App\Filament\Cashier\Resources\ProductunitResource\Pages;

use App\Filament\Cashier\Resources\ProductunitResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListProductunits extends ListRecords
{
    protected static string $resource = ProductunitResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
}
