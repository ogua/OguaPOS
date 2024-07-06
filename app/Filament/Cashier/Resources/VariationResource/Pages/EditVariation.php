<?php

namespace App\Filament\Cashier\Resources\VariationResource\Pages;

use App\Filament\Cashier\Resources\VariationResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditVariation extends EditRecord
{
    protected static string $resource = VariationResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }

    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('index');
    }
}
