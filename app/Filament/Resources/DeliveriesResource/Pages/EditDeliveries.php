<?php

namespace App\Filament\Resources\DeliveriesResource\Pages;

use App\Filament\Resources\DeliveriesResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditDeliveries extends EditRecord
{
    protected static string $resource = DeliveriesResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\ViewAction::make(),
            Actions\DeleteAction::make(),
        ];
    }

    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('index');
    }
}
