<?php

namespace App\Filament\Resources\CompanyinfoResource\Pages;

use App\Filament\Resources\CompanyinfoResource;
use Filament\Actions;
use Filament\Resources\Pages\CreateRecord;

class CreateCompanyinfo extends CreateRecord
{
    protected static string $resource = CompanyinfoResource::class;

    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('index');
    }
}
