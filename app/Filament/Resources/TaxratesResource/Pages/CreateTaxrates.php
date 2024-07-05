<?php

namespace App\Filament\Resources\TaxratesResource\Pages;

use App\Filament\Resources\TaxratesResource;
use Filament\Actions;
use Filament\Resources\Pages\CreateRecord;

class CreateTaxrates extends CreateRecord
{
    protected static string $resource = TaxratesResource::class;

    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('index');
    }
}
