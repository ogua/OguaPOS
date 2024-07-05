<?php

namespace App\Filament\Resources\ClientGroupResource\Pages;

use App\Filament\Resources\ClientGroupResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListClientGroups extends ListRecords
{
    protected static string $resource = ClientGroupResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
}
