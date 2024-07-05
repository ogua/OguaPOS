<?php

namespace App\Filament\Resources\CompanyinfoResource\Pages;

use App\Filament\Resources\CompanyinfoResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditCompanyinfo extends EditRecord
{
    protected static string $resource = CompanyinfoResource::class;

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
