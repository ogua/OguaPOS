<?php

namespace App\Filament\Cashier\Resources\PaymentResource\Pages;

use App\Filament\Cashier\Resources\PaymentResource;
use App\Models\Payment;
use Filament\Actions;
use Filament\Tables;
use Filament\Tables\Enums\FiltersLayout;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Filament\Forms\Components\DatePicker;
use Filament\Tables\Filters\Filter;
use Filament\Resources\Components\Tab;
use App\Models\PaymentAccount;
use Illuminate\Support\HtmlString;
use Filament\Resources\Pages\ListRecords;

class Accountbook extends ListRecords
{
    protected static string $resource = PaymentResource::class;

    public $record;
    
    public function getTitle() : string {
        return "Account Book";
    }
    
    public function getTabs(): array
    {
        return [
            'all' => Tab::make('All'),
            'Credit' => Tab::make('Credit')
            ->modifyQueryUsing(fn (Builder $query) => $query->where('payment_type',"credit")),
            'Debit' => Tab::make('Debit')
            ->modifyQueryUsing(fn (Builder $query) => $query->where('payment_type',"debit")),
        ];
    }
    
    public function table(Table $table): Table
    {
        $currentrecord = $this->record;
        
        return $table
        ->modifyQueryUsing(fn (Builder $query) => $query->where('account_id',$currentrecord)
        ->orderBy('paid_on','asc'))
        ->columns([
            Tables\Columns\TextColumn::make('paid_on')
            ->label('Date')
            ->sortable(),
            Tables\Columns\TextColumn::make('description')
           ->state(function ($record){
                    if ($record->purchase_id) {
                        return new HtmlString("
                            {$record->paying_type} <br>
                            Supplier: {$record->purchase?->suplier?->fullname} <br>
                            Reference No.: {$record->purchase?->reference_no} <br>
                            Pay reference no.: {$record->payment_ref} <br>
                            Payment For: {$record->purchase?->suplier?->fullname}
                        ");
                    }

                    if ($record->sale_id) {
                        return new HtmlString("
                            {$record->paying_type} <br>
                            Customer: {$record->sale?->customer?->name} <br>
                            Reference No.: {$record->sale?->reference_number} <br>
                            Pay reference no.: {$record->payment_ref} <br>
                            Payment For: {$record->sale?->customer?->name}
                        ");
                    }

                    if ($record->sale_return_id) {
                        return $record->salereturn->reference_no;
                    }

                    if ($record->purchase_return_id) {
                        return $record->sale->reference_no;
                    }
                }),
            Tables\Columns\TextColumn::make('payment_note')
            ->label('Note'),

            Tables\Columns\TextColumn::make('user.name')
            ->label('Action By')
            ->searchable(),

            Tables\Columns\TextColumn::make('credit')
            ->state(fn ($record) => $record->payment_type == "credit" ? "GHC ".$record->amount : "")
            ->sortable(),
            Tables\Columns\TextColumn::make('debit')
            ->state(fn ($record) => $record->payment_type == "debit" ? "GHC ".$record->amount : "")
            ->sortable(),

            Tables\Columns\TextColumn::make('balance')
            ->state(fn ($record) => "GHC ".$record->balance)
            ->sortable(),
            
            
            ])
            ->filters([
                Filter::make('created_at')
                ->form([
                    DatePicker::make('created_from'),
                    DatePicker::make('created_until'),
                    ])
                    ->query(function (Builder $query, array $data): Builder {
                        return $query
                        ->when(
                            $data['created_from'],
                            fn (Builder $query, $date): Builder => $query->whereDate('created_at', '>=', $date),
                            )
                            ->when(
                                $data['created_until'],
                                fn (Builder $query, $date): Builder => $query->whereDate('created_at', '<=', $date),
                            );
                        })
                        
                    ], layout: FiltersLayout::Modal)
                    ->actions([
                        Tables\Actions\DeleteAction::make()
                        ->visible(fn ($record) => !blank($record->fund_transfer_id))
                        ->before(function ($record) {
                            $from = $record->transfer_from;
                            $to = $record->transfer_to;
                            $amount = $record->amount;
                            
                            $fromacc = PaymentAccount::where('id',$from)->first();
                            $bal = $fromacc->current_balance;
                            $fromacc->current_balance = $bal + $amount;
                            $fromacc->save();
                            
                            $toacc = PaymentAccount::where('id',$to)->first();
                            $bal = $toacc->current_balance;
                            $toacc->current_balance = $bal - $amount;
                            $toacc->save();
                        }),
                    ]);
                }
}
