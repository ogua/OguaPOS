<?php

namespace App\Filament\Resources\PossettingsResource\Pages;

use App\Filament\Resources\PossettingsResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditPossettings extends EditRecord
{
    protected static string $resource = PossettingsResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }
}
