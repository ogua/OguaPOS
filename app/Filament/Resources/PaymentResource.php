<?php

namespace App\Filament\Resources;

use App\Filament\Resources\PaymentResource\Pages;
use App\Filament\Resources\PaymentResource\RelationManagers;
use App\Models\Giftcard;
use App\Models\Payment;
use App\Models\PaymentAccount;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Notifications\Notification;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Forms\Components\Actions\Action;
use Filament\Tables\Actions\ActionGroup;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class PaymentResource extends Resource
{
    protected static ?string $model = Payment::class;

    protected static ?string $navigationIcon = 'heroicon-o-currency-dollar';
    protected static ?string $slug = 'payment-account-report';
    protected static ?string $navigationGroup = 'Payment Accounts';
    protected static ?string $navigationLabel = 'Payment Account Report';
    protected static ?string $modelLabel = 'Payment Account Report';
    protected static ?int $navigationSort = 6;
    
    public static function getEloquentQuery(): Builder
    {
        return parent::getEloquentQuery()
        ->orderBy('id','desc');
    }

    public static function canCreate(): bool
    {
        return false;
    }

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Section::make('')
                    ->description('')
                    ->schema([
                        Forms\Components\TextInput::make('amount')
                        ->label('Amount paid')
                        ->prefix("GHC")
                        ->default(0),

                        Forms\Components\DateTimePicker::make('paid_on')
                        ->label('paid on')
                        ->default(0),
                    
                    Forms\Components\Select::make('paying_method')
                    ->options([
                        'CASH' => 'CASH',
                        'PAYPAL' => 'PAYPAL',
                        'CHEQUE' => 'CHEQUE',
                        'GIFT CARD' => 'GIFT CARD',
                        'CREDIT CARD' => 'CREDIT CARD',
                        'DRAFT' => 'DRAFT',
                        'BANK TRANSFER' => 'BANK TRANSFER'
                        ])
                        ->live()
                        ->searchable()
                        ->required(),
                        
                        // `cash_register_id`, 
                        // `account_id`, 
                        // `customer_id`,
                        
                        //payments
                        
                        Forms\Components\TextInput::make('bankname')
                        ->label("Bank name")
                        ->visible(fn ($get): bool => $get("paying_method") == "BANK TRANSFER")
                        ->required(),
                        
                        Forms\Components\TextInput::make('accountnumber')
                        ->label("Account number")
                        ->visible(fn ($get): bool => $get("paying_method") == "BANK TRANSFER")
                        ->required(),
                        
                        Forms\Components\TextInput::make('cheque_no')
                        ->label("Cheque number")
                        ->visible(fn ($get): bool => $get("paying_method") == "CHEQUE")
                        ->required(),
                        
                        Forms\Components\Hidden::make('gift_card_id')
                        ->visible(fn ($get): bool => $get("paying_method") == "GIFT CARD")
                        ->required(),
                        
                        Forms\Components\TextInput::make('gift_card')
                        ->label("Enter Gift Card Code")
                        ->visible(fn ($get): bool => $get("paying_method") == "GIFT CARD")
                        ->helperText("You can only proceed after the gift card has been successfully added")
                        ->default(0)
                        ->suffixAction(
                            Action::make("check")
                            ->icon('heroicon-m-clipboard')
                            ->label("Check Card")
                            ->action(function($state, Forms\Set $set, Forms\Get $get){
                                
                                $card = $state;
                                $paying = $get("amount");
                                
                                $cards = Giftcard::where('is_active',true)
                                ->where('expiry_date', '>=',date('Y-m-d'))
                                ->where('card_no',$card)
                                ->first();
                                
                                if($cards){
                                    
                                    //check balance
                                    $amount = $cards->amount;
                                    $exp = $cards->expense;
                                    
                                    $bal = (int) $amount - (int) $exp;
                                    
                                    if($paying > $bal){
                                        
                                        Notification::make()
                                        ->title("Amount exceeds card balance! Gift Card balance: {$bal}!")
                                        ->warning()
                                        ->send();
                                        
                                    }else {
                                        
                                        Notification::make()
                                        ->title("Gift card added successfully!")
                                        ->success()
                                        ->send();
                                        
                                        $set("gift_card_id",$cards->id);
                                    }
                                    
                                }else{
                                    
                                    Notification::make()
                                    ->title("Gift Card expired or dont exist!")
                                    ->warning()
                                    ->send();
                                    
                                    $set("gift_card_id","");
                                }
                                
                            })
                        ),
                        
                        Forms\Components\Hidden::make('user_id')
                        ->default(auth()->user()->id),
                        
                        Forms\Components\Hidden::make('change')
                        ->default(0),
                        
                        Forms\Components\Select::make('account_id')
                        ->label('Payment account')
                        ->options(PaymentAccount::pluck('account_name','id'))
                        ->preload()
                        ->searchable(),
                        
                        Forms\Components\Hidden::make('customer_id'),

                        Forms\Components\Textarea::make('payment_note')
                            ->columnSpanFull(),
                        
                    ])
                    ->columns(2),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->recordUrl("")
            ->columns([
                Tables\Columns\TextColumn::make('paid_on')
                ->label('Date')
                ->sortable(),
                Tables\Columns\TextColumn::make('payment_ref')
                ->label('Payment Ref No.')
                ->searchable(),

                Tables\Columns\TextColumn::make('reference')
                ->label('Reference no')
                ->state(function ($record){
                    if ($record->purchase_id) {
                        return $record->purchase->reference_no;
                    }

                    if ($record->sale_id) {
                        return $record->sale->reference_number ?? "";
                    }

                    if ($record->sale_return_id) {
                        return $record->salereturn->reference_no;
                    }

                    if ($record->purchase_return_id) {
                        return $record->purchasereturn->purchase->reference_no;
                    }
                })
                ->searchable(),

                Tables\Columns\TextColumn::make('paying_type')
                ->label('Payment type')
                ->searchable(),

                Tables\Columns\TextColumn::make('account.account_name')
                ->label('Account')
                ->state(fn (Payment $record) => $record->account->account_name." - ".$record->account->account_number)
                ->searchable(),
                
            ])
            ->filters([
                //
            ])
           ->actions([
           
                Tables\Actions\Action::make("linkaccount")
                ->label('Link Account')
                ->icon('heroicon-m-banknotes')
                ->form([
                    Forms\Components\Select::make('payment_account')
                    ->label('Account')
                    ->options(PaymentAccount::pluck('account_name','id'))
                    ->preload()
                    ->searchable()
                    ->required(),
                ])
                ->action(function (Payment $record, array $data){
                    //previous account
                    $prev = $record->account_id;
                    $now = $data['payment_account'];

                    if ($record->account_id == "$now") {
                        return;
                    }elseif (isset($record->account_id) && $record->account_id != "$now") {
                        $previous =  PaymentAccount::where('id',$record->account_id)->first();
                        $previous->current_balance -= $record->amount;
                        $previous->save();
                    }

                    $record->account_id = $data['payment_account'];
                    $record->save();

                    //update account.
                    $paycc = PaymentAccount::where('id',$data["payment_account"])->first();
                    $balance = $paycc->current_balance + $record->amount;
                    $paycc->current_balance = $balance;
                    $paycc->save();

                    Notification::make()
                    ->title('Linked successfully!')
                    ->success()
                    ->body($paycc->account_name." linked to {$record->payment_ref} successfully")
                    ->send();


                }),   
    ]);
    }

    public static function getRelations(): array
    {
        return [
            //
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListPayments::route('/'),
            'create' => Pages\CreatePayment::route('/create'),
            'edit' => Pages\EditPayment::route('/{record}/edit'),
            'account-book' => Pages\Accountbook::route('/{record}/account-book'),
            'payment-account-book' => Pages\PaymentAccountBook::route('/{record}/payments-account-book')
        ];
    }
}
