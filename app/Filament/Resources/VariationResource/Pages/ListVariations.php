<?php

namespace App\Filament\Resources\VariationResource\Pages;

use App\Filament\Resources\VariationResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListVariations extends ListRecords
{
    protected static string $resource = VariationResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
}
