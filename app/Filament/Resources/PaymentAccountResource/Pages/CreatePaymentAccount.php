<?php

namespace App\Filament\Resources\PaymentAccountResource\Pages;

use App\Filament\Resources\PaymentAccountResource;
use Filament\Actions;
use Filament\Resources\Pages\CreateRecord;

class CreatePaymentAccount extends CreateRecord
{
    protected static string $resource = PaymentAccountResource::class;

    protected function getRedirectUrl(): string
    {
        return $this->previousUrl ?? $this->getResource()::getUrl('index');
    }
}
