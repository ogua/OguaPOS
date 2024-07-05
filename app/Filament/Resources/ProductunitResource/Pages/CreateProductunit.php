<?php

namespace App\Filament\Resources\ProductunitResource\Pages;

use App\Filament\Resources\ProductunitResource;
use Filament\Actions;
use Filament\Resources\Pages\CreateRecord;

class CreateProductunit extends CreateRecord
{
    protected static string $resource = ProductunitResource::class;

    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('index');
    }
}
