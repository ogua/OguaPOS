<?php

namespace App\Filament\Cashier\Widgets\Cashier;

use App\Models\Sales;
use Filament\Tables;
use Filament\Tables\Table;
use Filament\Widgets\TableWidget as BaseWidget;

class RecentTransactions extends BaseWidget
{
    protected static ?int $sort = 2;

    protected int | string | array $columnSpan = 'full';

     /**
     * Widget Title
     *
     * @var string|null
     */
    protected static ?string $heading = 'Recent Transactions';
    
    public function table(Table $table): Table
    {
        return $table
            ->query(
                Sales::query()
                ->where('user_id',auth()->user()->id)
                ->latest()->take(10)
            )
            ->columns([
                Tables\Columns\TextColumn::make('transaction_date')
                ->label('Date')
                ->date()
                ->sortable(),
                Tables\Columns\TextColumn::make('reference_number')
                ->label('Reference')
                ->searchable(),
                Tables\Columns\TextColumn::make('customer.name')
                ->searchable(),
                Tables\Columns\TextColumn::make('sale_status')
                ->searchable(),
                Tables\Columns\TextColumn::make('payment_status')
                ->searchable(),
                Tables\Columns\TextColumn::make('item')
                ->numeric()
                ->sortable()
                ->toggleable(isToggledHiddenByDefault: true),
                Tables\Columns\TextColumn::make('total_qty')
                ->numeric()
                ->sortable()
                ->toggleable(isToggledHiddenByDefault: true),
                Tables\Columns\TextColumn::make('total_price')
                ->formatStateUsing(fn (string $state): string => "GHC ".number_format($state,2))
                ->sortable()
                ->toggleable(isToggledHiddenByDefault: true),
                Tables\Columns\TextColumn::make('grand_total')
                ->formatStateUsing(fn (string $state): string => "GHC ".number_format($state,2))
                ->sortable(),
            ]);
    }
}
