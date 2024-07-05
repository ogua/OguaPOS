<?php

namespace App\Filament\Resources\TaxratesResource\Pages;

use App\Filament\Resources\TaxratesResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListTaxrates extends ListRecords
{
    protected static string $resource = TaxratesResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
}
