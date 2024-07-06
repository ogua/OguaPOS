<?php

namespace App\Filament\Cashier\Resources\VariationResource\Pages;

use App\Filament\Cashier\Resources\VariationResource;
use Filament\Actions;
use Filament\Resources\Pages\CreateRecord;

class CreateVariation extends CreateRecord
{
    protected static string $resource = VariationResource::class;

    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('index');
    }
}
