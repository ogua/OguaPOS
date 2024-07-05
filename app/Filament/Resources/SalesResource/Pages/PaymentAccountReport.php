<?php

namespace App\Filament\Resources\SalesResource\Pages;

use App\Filament\Resources\SalesResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class PaymentAccountReport extends ListRecords
{
    protected static string $resource = SalesResource::class;

    protected static ?string $slug = 'payment-account-report';
}
