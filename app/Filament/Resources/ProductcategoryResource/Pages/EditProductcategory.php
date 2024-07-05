<?php

namespace App\Filament\Resources\ProductcategoryResource\Pages;

use App\Filament\Resources\ProductcategoryResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditProductcategory extends EditRecord
{
    protected static string $resource = ProductcategoryResource::class;

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
