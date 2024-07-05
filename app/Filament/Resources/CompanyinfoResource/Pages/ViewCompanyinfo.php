<?php

namespace App\Filament\Resources\CompanyinfoResource\Pages;

use App\Filament\Resources\CompanyinfoResource;
use Filament\Actions;
use Filament\Resources\Pages\ViewRecord;

class ViewCompanyinfo extends ViewRecord
{
    protected static string $resource = CompanyinfoResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\EditAction::make(),
        ];
    }
}
