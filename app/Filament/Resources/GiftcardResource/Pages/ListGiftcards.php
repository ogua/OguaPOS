<?php

namespace App\Filament\Resources\GiftcardResource\Pages;

use App\Filament\Resources\GiftcardResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListGiftcards extends ListRecords
{
    protected static string $resource = GiftcardResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
}
