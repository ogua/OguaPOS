<?php

namespace App\Filament\Resources\FundTransferResource\Pages;

use App\Filament\Resources\FundTransferResource;
use App\Models\FundTransfer;
use App\Models\PaymentAccount;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;
use Filament\Tables;
use Filament\Tables\Enums\FiltersLayout;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Filament\Forms\Components\DatePicker;
use Filament\Tables\Filters\Filter;
use Filament\Resources\Components\Tab;

class Accountbook extends ListRecords
{
    protected static string $resource = FundTransferResource::class;
    
    public $record;
    
    public function getTitle() : string {
        return "Account Book";
    }
    
    public function getTabs(): array
    {
        return [
            'all' => Tab::make('All'),
            'Credit' => Tab::make('Credit')
            ->modifyQueryUsing(fn (Builder $query) => $query->where('transfer_from',$this->record)),
            'Debit' => Tab::make('Debit')
            ->modifyQueryUsing(fn (Builder $query) => $query->where('transfer_to',$this->record)),
        ];
    }
    
    public function table(Table $table): Table
    {
        $currentrecord = $this->record;
        
        return $table
        ->modifyQueryUsing(fn (Builder $query) => $query->where('transfer_from',$currentrecord)
        ->orWhere('transfer_to',$currentrecord))
        ->columns([
            Tables\Columns\TextColumn::make('transfer_date')
            ->label('Date')
            ->date()
            ->sortable(),
            Tables\Columns\TextColumn::make('description')
            ->state(function (FundTransfer $record)  use ($currentrecord) {
                if($currentrecord == $record->transfer_from){
                    return "Fund Transfer:  (To: {$record->accto?->account_name}";
                }else{
                    return "Fund Transfer:  (From: {$record->accfrom?->account_name}";
                }
            }),
            Tables\Columns\TextColumn::make('note')
            ->label('Note')
            ->date()
            ->sortable(),
            Tables\Columns\TextColumn::make('user.name')
            ->label('Action By')
            ->sortable(),
            Tables\Columns\TextColumn::make('credit')
            ->state(function (FundTransfer $record)  use ($currentrecord) {
                if($currentrecord == $record->transfer_from){
                    return (auth()->user()->pos?->currncy?->currency_code ?? 'GHC').$record->amount;
                }else{
                    return "";
                }
            })
            ->sortable(),
            Tables\Columns\TextColumn::make('debit')
            ->state(function (FundTransfer $record)  use ($currentrecord) {
                if($currentrecord == $record->transfer_from){
                    return "";
                }else{
                    return (auth()->user()->pos?->currncy?->currency_code ?? 'GHC').$record->amount;
                }
            })
            ->sortable(),
            
            Tables\Columns\TextColumn::make('balance')
            ->state(function (FundTransfer $record)  use ($currentrecord) {
                if($currentrecord == $record->transfer_from){
                    return (auth()->user()->pos?->currncy?->currency_code ?? 'GHC').$record->from_balance;
                }else{
                    return (auth()->user()->pos?->currncy?->currency_code ?? 'GHC').$record->to_balance;
                }
            })
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
            