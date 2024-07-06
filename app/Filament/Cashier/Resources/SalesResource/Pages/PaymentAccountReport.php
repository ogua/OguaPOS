<?php

namespace App\Filament\Cashier\Resources\SalesResource\Pages;

use App\Filament\Cashier\Resources\SalesResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class PaymentAccountReport extends ListRecords
{
    protected static string $resource = SalesResource::class;

    protected static ?string $slug = 'payment-account-report';
}
