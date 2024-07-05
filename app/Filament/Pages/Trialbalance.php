<?php

namespace App\Filament\Pages;

use App\Models\PaymentAccount;
use Filament\Pages\Page;

class Trialbalance extends Page
{
    protected static ?string $navigationIcon = 'heroicon-o-banknotes';
    protected static ?string $slug = 'trial-balance';
    protected static ?string $navigationGroup = 'Payment Accounts';
    protected static ?string $navigationLabel = 'Trial Balance';
    protected static ?string $modelLabel = 'Trial Balance';
    protected static ?int $navigationSort = 4;

    protected static string $view = 'filament.pages.trialbalance';

    public $accounts;

    public function getTitle() : string {
        return "";
    }

    public function mount()
    {
        $this->accounts = PaymentAccount::all();
    }
}
