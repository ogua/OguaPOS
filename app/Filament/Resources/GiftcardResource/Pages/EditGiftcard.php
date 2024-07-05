<?php

namespace App\Filament\Resources\GiftcardResource\Pages;

use App\Filament\Resources\GiftcardResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditGiftcard extends EditRecord
{
    protected static string $resource = GiftcardResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }
}
