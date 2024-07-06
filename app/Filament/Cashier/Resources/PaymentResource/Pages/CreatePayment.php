<?php

namespace App\Filament\Cashier\Resources\PaymentResource\Pages;

use App\Filament\Cashier\Resources\PaymentResource;
use Filament\Actions;
use Filament\Resources\Pages\CreateRecord;

class CreatePayment extends CreateRecord
{
    protected static string $resource = PaymentResource::class;
}
