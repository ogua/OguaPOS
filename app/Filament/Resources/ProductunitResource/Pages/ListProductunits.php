<?php

namespace App\Filament\Resources\ProductunitResource\Pages;

use App\Filament\Resources\ProductunitResource;
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
