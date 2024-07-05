<?php

namespace App\Filament\Resources\CompanyinfoResource\Pages;

use App\Filament\Resources\CompanyinfoResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListCompanyinfos extends ListRecords
{
    protected static string $resource = CompanyinfoResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
}
