<?php

namespace App\Filament\Resources\ClientGroupResource\Pages;

use App\Filament\Resources\ClientGroupResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditClientGroup extends EditRecord
{
    protected static string $resource = ClientGroupResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }
}
