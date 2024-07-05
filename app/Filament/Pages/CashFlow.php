<?php

namespace App\Filament\Pages;

use Filament\Pages\Page;

class CashFlow extends Page
{
    protected static ?string $navigationIcon = 'heroicon-o-banknotes';
    protected static ?string $slug = 'cash-flow';
    protected static ?string $navigationGroup = 'Payment Accounts';
    protected static ?string $navigationLabel = 'Cash Flow';
    protected static ?string $modelLabel = 'Cash Flow';
    protected static ?int $navigationSort = 3;

    protected static string $view = 'filament.pages.cash-flow';

    public function getTitle() : string {
        return "";
    }
}
