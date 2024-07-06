<?php

namespace App\Filament\Cashier\Resources\ProductcategoryResource\Pages;

use App\Filament\Cashier\Resources\ProductcategoryResource;
use Filament\Actions;
use Filament\Resources\Pages\CreateRecord;

class CreateProductcategory extends CreateRecord
{
    protected static string $resource = ProductcategoryResource::class;

    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('index');
    }
}
