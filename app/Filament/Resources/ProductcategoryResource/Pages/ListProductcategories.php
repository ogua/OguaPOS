<?php

namespace App\Filament\Resources\ProductcategoryResource\Pages;

use App\Filament\Resources\ProductcategoryResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListProductcategories extends ListRecords
{
    protected static string $resource = ProductcategoryResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
}
