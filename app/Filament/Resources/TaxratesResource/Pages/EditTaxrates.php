<?php

namespace App\Filament\Resources\TaxratesResource\Pages;

use App\Filament\Resources\TaxratesResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditTaxrates extends EditRecord
{
    protected static string $resource = TaxratesResource::class;

    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('index');
    }

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }
}
