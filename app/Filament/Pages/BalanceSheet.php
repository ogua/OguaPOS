<?php

namespace App\Filament\Pages;

use App\Models\PaymentAccount;
use Filament\Pages\Page;

class BalanceSheet extends Page
{
    protected static ?string $navigationIcon = 'heroicon-o-banknotes';
    protected static ?string $slug = 'balance-sheet';
    protected static ?string $navigationGroup = 'Payment Accounts';
    protected static ?string $navigationLabel = 'Balance Sheet';
    protected static ?string $modelLabel = 'Balance Sheet';
    protected static ?int $navigationSort = 5;

    protected static string $view = 'filament.pages.balance-sheet';

    public $accounts;

    public function getTitle() : string {
        return "";
    }

    public function mount()
    {
        $this->accounts = PaymentAccount::all();
    }
}
