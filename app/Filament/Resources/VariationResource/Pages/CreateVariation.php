<?php

namespace App\Filament\Resources\VariationResource\Pages;

use App\Filament\Resources\VariationResource;
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
