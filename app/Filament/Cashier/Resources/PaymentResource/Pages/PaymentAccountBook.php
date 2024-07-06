<?php

namespace App\Filament\Cashier\Resources\PaymentResource\Pages;

use App\Filament\Cashier\Resources\PaymentResource;
use App\Models\Payment;
use App\Models\PaymentAccount;
use Filament\Resources\Pages\Page;
use Livewire\WithPagination;


class PaymentAccountBook extends Page
{
    use WithPagination;

    protected static string $resource = PaymentResource::class;

    protected static string $view = 'filament.resources.payment-resource.pages.payment-account-book';

    public $record;
}
