<?php

namespace App\Filament\Resources\PossettingsResource\Pages;

use App\Filament\Resources\PossettingsResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListPossettings extends ListRecords
{
    protected static string $resource = PossettingsResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
}
