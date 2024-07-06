<?php

namespace App\Filament\Cashier\Resources\ProductunitResource\Pages;

use App\Filament\Cashier\Resources\ProductunitResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditProductunit extends EditRecord
{
    protected static string $resource = ProductunitResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
             Actions\ViewAction::make(),
        ];
    }

    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('index');
    }
}
