<?php

namespace App\Filament\Resources;

use App\Filament\Resources\PurchaseReturnResource\Pages;
use App\Filament\Resources\PurchaseReturnResource\RelationManagers;
use App\Models\PurchaseReturn;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use Icetalker\FilamentTableRepeater\Forms\Components\TableRepeater;
use App\Livewire\Sound;
use App\Models\Giftcard;
use App\Models\Payment;
use App\Models\PaymentAccount;
use App\Models\Purchase;
use App\Models\SaleReturn;
use App\Models\Sales;
use App\Models\Taxrates;
use App\Partials\Enums\PaymentStatus;
use Filament\Infolists\Components\TextEntry;
use Filament\Tables\Actions\ActionGroup;
use Filament\Forms\Components\Actions\Action;
use Filament\Infolists\Components\Actions;
use Filament\Infolists\Components\Actions\Action as ActionsAction;
use Livewire\Component as Livewire;
use Filament\Notifications\Notification;
use Filament\Tables\Enums\ActionsPosition;
use Filament\Infolists\Components\RepeatableEntry;
use Filament\Support\Enums\MaxWidth;
use Filament\Tables\Columns\Summarizers\Sum;
use Illuminate\Contracts\View\View;

class PurchaseReturnResource extends Resource
{
    protected static ?string $model = PurchaseReturn::class;

    protected static ?string $navigationIcon = 'heroicon-o-truck';
    protected static ?string $slug = 'purchase-return';
    protected static ?string $navigationGroup = 'Purchases';
    protected static ?string $navigationLabel = 'Purchase return';
    protected static ?string $modelLabel = 'Purchase Return';
    protected static ?int $navigationSort = 2;

    public static function getEloquentQuery(): Builder
    {
        return parent::getEloquentQuery()
        ->orderBy('id','desc');
    }

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                    Forms\Components\Section::make('')
                        ->description('')
                            ->schema([
                        Forms\Components\Hidden::make(Sound::class),
                        Forms\Components\DateTimePicker::make('returndate')
                        ->label('Date returned')
                            ->required(),
                        Forms\Components\Select::make('puchase_id')
                            ->label("Purchase Reference Number")
                            ->required()
                            ->options(Purchase::latest()->pluck('reference_no','id'))
                            ->preload()
                            ->searchable()
                            ->live()
                            ->afterStateUpdated(function ($state,$set,$get){
                                $set('items',[]);

                                if (empty($state)) {
                                   static::calculateitems($set,$get);
                                   return;
                                }

                                $repeaterItems = $get('items');

                                $purchase = Purchase::findorfail($state);
                                $set("reference_no",$purchase->reference_number);
                                $set("total_qty",$purchase->total_qty);
                                $set("total_discount",$purchase->total_discount);
                                //$set("total_tax",$purchase->order_tax);
                                $set("item",$purchase->item);

                                foreach ($purchase->purchaseitmes as $key => $item) {

                                    $data = [
                                        'purchase_return_id' => $item->id,
                                        'product_name' =>  $item->product_name,
                                        'product_id' => $item->product_id,
                                        'variant_id' => $item->variant_id,
                                        'unit_price' => $item->unit_price,
                                        'qty_orderd' => $item->qty,
                                        'qty' => 0,
                                        'total' => $item->total,
                                        'sale_unit_id' => $item->sale_unit_id,
                                        'discount' => $item->discount,
                                        'tax_rate' => $item->tax_rate,
                                        'tax' => 0,
                                        'warehouse_id' => $purchase->warehouse_id
                                    ];

                                     array_push($repeaterItems, $data);
                                }

                                $set('items', $repeaterItems);

                                self::updateTotals($get,$set);

                            }),
                        Forms\Components\Hidden::make('user_id')
                            ->default(auth()->user()->id),
                        Forms\Components\Select::make('account_id')
                            ->label('Account')
                            ->options(PaymentAccount::pluck('account_name','id'))
                            ->preload()
                            ->searchable()
                            ->required(),
                        Forms\Components\Hidden::make('reference_no'),

                        TableRepeater::make('items')
                        ->label("")
                        ->allowHtml()
                        ->addable(false)
                        ->live()
                        ->afterStateUpdated(function($state,Forms\Get $get, Forms\Set $set){
                            
                            self::updateTotals($get,$set);
                            
                        })
                        ->deleteAction(
                            function(Action $action) {
                                $action->after(fn(Forms\Get $get, Forms\Set $set) => self::updateTotals($get, $set));
                            }
                            )
                            ->reorderable(false)
                            ->relationship('returnitems')
                            ->schema([
                                Forms\Components\Hidden::make('product_id'),
                                Forms\Components\Hidden::make('variant_id'),
                                Forms\Components\Hidden::make('sale_unit_id'),
                                Forms\Components\Hidden::make('purchase_return_id')
                                ->dehydrated(false),
                                
                                Forms\Components\TextInput::make('product_name')
                                ->columnSpan(2),
                                
                                Forms\Components\TextInput::make('unit_price')
                                ->type("number")
                                ->readOnly()
                                ->label('Price')
                                ->required(),

                                Forms\Components\TextInput::make('qty_orderd')
                                ->type("number")
                                ->label('Qty Purchased')
                                ->readOnly()
                                ->dehydrated(false),
                                
                                Forms\Components\TextInput::make('qty')
                                ->label('Quantity Returned')
                                ->integer()
                                ->default(1)
                                ->live(onBlur: true)
                                ->afterStateUpdated(function (Forms\Get $get,Forms\Set $set, $state,Livewire $livewire){
                                    $pxqty = $get("qty_orderd");

                                    if ($state > $pxqty) {
                                        $livewire->dispatch('play-error');
                                        $set("qty",$pxqty);
                                       
                                         self::updateTotals($get,$set);
                                        return;

                                    }elseif($state < 0){
                                        $livewire->dispatch('play-error');
                                        $set("qty",0);
                                        self::updateTotals($get,$set);
                                        return;
                                    }

                                    $tot = $state * $get('unit_price');
                                    $set("tax",(($get("tax_rate") / 100) * $tot));

                                    $total = ($tot + (($get("tax_rate") / 100) * $tot));
                                    $set("total",$total);

                                    //self::calculateitems($set,$get);

    
                                })
                                ->afterStateHydrated(function (Forms\Get $get,Forms\Set $set, $state, Livewire $livewire){
                                                                       
                                    $tot = $state * $get('unit_price');
                                    $set("tax",(($get("tax_rate") / 100) * $tot));
                                    $set("total",($tot + (($get("tax_rate") / 100) * $tot)));

                                    //self::updateTotals($get,$set);
                                })
                                ->required(),
                                
                                Forms\Components\Hidden::make('discount'),
                                Forms\Components\TextInput::make('tax_rate')
                                ->readOnly(),
                                Forms\Components\TextInput::make('tax')
                                ->readOnly(),

                                Forms\Components\Placeholder::make('ptotal')
                                ->content(function ($get,$set){
                                    $tot = (int) $get("qty") * (int) $get('unit_price');

                                    $set("total",($tot + ($get("tax_rate") / 100) * $tot));  
                                    return ($tot + ($get("tax_rate") / 100) * $tot);
                                })
                                ->label('Total'),
                                
                                Forms\Components\Hidden::make('total')
                                ->default(0),
                                ])
                                ->reorderable()
                                //->cloneable()
                                ->collapsible()
                                ->defaultItems(0)
                                ->columnSpan('full')
                                ->visibleOn('create'),


                        TableRepeater::make('items')
                        ->label("")
                        ->addable(false)
                        ->live()
                        ->afterStateUpdated(function($state,Forms\Get $get, Forms\Set $set){
                            
                            self::updateTotals($get,$set);
                            
                        })
                        ->deleteAction(
                            function(Action $action) {
                                $action->after(fn(Forms\Get $get, Forms\Set $set) => self::updateTotals($get, $set));
                            }
                            )
                            ->reorderable(false)
                            ->relationship('returnitems')
                            ->schema([
                                Forms\Components\Hidden::make('product_id'),
                                Forms\Components\Hidden::make('variant_id'),
                                Forms\Components\Hidden::make('sale_unit_id'),
                                Forms\Components\Hidden::make('purchase_return_id')
                                ->dehydrated(false),
                                
                                Forms\Components\TextInput::make('product_name')
                                ->columnSpan(2),
                                
                                Forms\Components\TextInput::make('unit_price')
                                ->type("number")
                                ->readOnly()
                                ->label('Price')
                                ->required(),
                                
                                Forms\Components\TextInput::make('qty')
                                ->label('Quantity Returned')
                                ->integer()
                                ->default(1)
                                ->live(onBlur: true)
                                ->afterStateUpdated(function (Forms\Get $get,Forms\Set $set, $state,Livewire $livewire){
                                    $pxqty = $get("qty_orderd");

                                    if ($state > $pxqty) {
                                        $livewire->dispatch('play-error');
                                        $set("qty",$pxqty);
                                       
                                         self::updateTotals($get,$set);
                                        return;

                                    }elseif($state < 0){
                                        $livewire->dispatch('play-error');
                                        $set("qty",0);
                                        self::updateTotals($get,$set);
                                        return;
                                    }

                                    $tot = $state * $get('unit_price');
                                    $set("tax",(($get("tax_rate") / 100) * $tot));

                                    $total = ($tot + (($get("tax_rate") / 100) * $tot));
                                    $set("total",$total);

                                    //self::calculateitems($set,$get);

    
                                })
                                ->afterStateHydrated(function (Forms\Get $get,Forms\Set $set, $state, Livewire $livewire){
                                                                       
                                    $tot = $state * $get('unit_price');
                                    $set("tax",(($get("tax_rate") / 100) * $tot));
                                    $set("total",($tot + (($get("tax_rate") / 100) * $tot)));

                                    //self::updateTotals($get,$set);
                                })
                                ->required(),
                                
                                Forms\Components\Hidden::make('discount'),
                                Forms\Components\TextInput::make('tax_rate')
                                ->readOnly(),
                                Forms\Components\TextInput::make('tax')
                                ->readOnly(),

                                Forms\Components\Placeholder::make('ptotal')
                                ->content(function ($get,$set){
                                    $tot = (int) $get("qty") * (int) $get('unit_price');

                                    $set("total",($tot + ($get("tax_rate") / 100) * $tot));  
                                    return ($tot + ($get("tax_rate") / 100) * $tot);
                                })
                                ->label('Total'),
                                
                                Forms\Components\Hidden::make('total')
                                ->default(0),
                                ])
                                ->reorderable()
                                //->cloneable()
                                ->collapsible()
                                ->defaultItems(0)
                                ->columnSpan('full')
                                ->visibleOn('edit'),

                                Forms\Components\Placeholder::make('totalitem')
                                ->content(function ($get,$set){
                                    
                                    return "Total item: ".$get("item");
                                })
                                ->label(''),

                                Forms\Components\Placeholder::make('totqty')
                                ->content(function ($get,$set){
                                    
                                    return "Total items returned: ".$get("total_qty");
                                })
                                ->label(''),

                        
                                Forms\Components\Placeholder::make('ptottax')
                                ->content(function ($get,$set){ 
                                    
                                    return "Total tax: ".number_format($get("total_tax"),2);
                                })
                                ->label(''),
                                
                                Forms\Components\Placeholder::make('item')
                                ->content(function ($get,$set){
                                    
                                    return "Subtotal: ".number_format($get("total_amount"),2);
                                })
                                ->label(''),

                            Forms\Components\Hidden::make('total_qty'),
                            Forms\Components\Hidden::make('total_discount')
                            ->default(0),
                            Forms\Components\Hidden::make('total_tax'),
                            Forms\Components\Hidden::make('item'),
                            Forms\Components\Select::make('order_tax')
                            ->label('Purchase tax')
                            ->required()
                            ->options(Taxrates::pluck('name','rate'))
                            ->preload()
                            ->searchable()
                            ->default(0)
                            ->live()
                            ->afterStateUpdated(function ($state,$get,$set){
                                self::updateTotals($get,$set);
                            })
                            ->columnSpan(2),
                            Forms\Components\FileUpload::make('document')
                            ->columnSpan(2),
                            Forms\Components\Hidden::make('grand_total'),
                            Forms\Components\Textarea::make('return_note')
                                ->columnSpan(2),
                            Forms\Components\Textarea::make('staff_note')
                                ->columnSpan(2),
                            
                            Forms\Components\Hidden::make('payment_status')
                                ->default(1),
                            Forms\Components\Hidden::make('total_amount'),
                            Forms\Components\Hidden::make('amount_paid')
                            ->default(0),
                            Forms\Components\Hidden::make('amount_due'),
                           
                            Forms\Components\Placeholder::make('totqty')
                            ->content(function ($get,$set){
                                
                                return "Total items returned: ".$get("total_qty");
                            })
                            ->label(''),

                                Forms\Components\Placeholder::make('ptottax')
                                ->content(function ($get,$set){ 
                                    
                                    return "Order Tax: ".$get("order_tax")."%";
                                })
                                ->label(''),

                        
                                Forms\Components\Placeholder::make('ptottax')
                                ->content(function ($get,$set){

                                    $ordertax = (int) $get("order_tax");

                                    $otax = ($ordertax / 100) * $get("total_amount");
                                    
                                    return "Tax value: ".$otax;
                                })
                                ->label(''),
                                
                                Forms\Components\Placeholder::make('item')
                                ->content(function ($get,$set){
                                    
                                    return "Grand Total: ".number_format($get("grand_total"),2);
                                })
                                ->label(''),

                                
                 ])
                    ->columns(4),
            ]);
    }

    public static function calculateitems($set,$get) {
                                                                    
        $state = $get('items');
        
        $totitem = count(collect($state));
        $set("item",$totitem);
        
        $totalqty = collect($state)
        ->pluck('qty')
        ->sum();

        $tottax = collect($state)
        ->pluck('tax')
        ->sum();

        $set("total_tax",$tottax);
        
        $set("total_qty",$totalqty);
        
    }

    // This function updates totals based on the selected products and quantities
    public static function updateTotals(Forms\Get $get, Forms\Set $set): void
    {                
        // Retrieve all selected products and remove empty rows
        $selectedProducts = collect($get('items'))->filter(fn($item) => !empty($item['product_id']) && !empty($item['qty']));
        
        $qty = $selectedProducts->pluck('qty')->sum();
        $set("total_qty",$qty);

        //logger($selectedProducts);
        
        $prices = [];
        $tax = [];
        
        foreach ($selectedProducts as $selectedProduct) {
                        
            $tot = $selectedProduct["qty"] * $selectedProduct['unit_price'];

            $total = ($tot + ($selectedProduct["tax_rate"] / 100) * $tot);

            $ctax = ($selectedProduct["tax_rate"] / 100) * $tot;

            $prices[$selectedProduct['product_id']] = $total;
            $tax[$selectedProduct['product_id']] = $ctax;
        }
        
        // Calculate subtotal based on the selected products and quantities
        $subtotal = $selectedProducts->reduce(function ($subtotal, $product) use ($prices) {
            return $subtotal + $prices[$product['product_id']];
        }, 0);

        $totaltax = $selectedProducts->reduce(function ($totaltax, $product) use ($tax) {
            return $totaltax + $tax[$product['product_id']];
        }, 0);

        $set("total_tax",$totaltax);
        
        $ordertax = (int) $get("order_tax");

        $otax = ($ordertax / 100) * $subtotal;
        
        $grandtotal = ($subtotal + $otax );
                                                                            
        // Update the state with the new values
        $set('total_amount', number_format($subtotal, 2, '.', ''));
        $set('grand_total', number_format($grandtotal, 2, '.', ''));

        $paid = $get("amount_paid");

        $left = $grandtotal - $paid;
        $set("amount_due",$left);
        $set("amount_paid",0);
                
        //static::calculateitems($set,$get);
    }             

    public static function table(Table $table): Table
    {
        return $table
            ->recordUrl("")
            ->columns([
                Tables\Columns\TextColumn::make('returndate')
                    ->label("Returned date")
                    ->date()
                    ->sortable(),
                Tables\Columns\TextColumn::make('purchase.reference_no')
                    ->label('Reference no')
                    ->searchable(),
                Tables\Columns\TextColumn::make('sale.warehouse.name')
                ->label('Warehouse')
                    ->searchable(),
                Tables\Columns\TextColumn::make('purchase.suplier.fullname')
                ->label('Supplier')
                ->searchable(),
                Tables\Columns\TextColumn::make('payment_status')
                    ->searchable(),
                // Tables\Columns\TextColumn::make('total_amount')
                //     ->numeric()
                //     ->sortable(),
                // Tables\Columns\TextColumn::make('order_tax')
                //     ->numeric()
                //     ->sortable(),
                Tables\Columns\TextColumn::make('grand_total')
                    ->label('Total amount')
                    ->state(function ($record){
                        return "GHC ".$record->grand_total;
                    })
                    ->badge()
                    ->color('success')
                    ->sortable(),
                Tables\Columns\TextColumn::make('amount_due')
                    ->numeric()
                    ->state(function ($record){
                        return "GHC ".$record->amount_due;
                    })
                    ->badge()
                    ->summarize(Sum::make()->money('GHC')->label('Total due payments')),

                Tables\Columns\TextColumn::make('created_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                Tables\Columns\TextColumn::make('updated_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                //
            ])
            ->actions([
                ActionGroup::make([
                    //Tables\Actions\ViewAction::make(),
                    Tables\Actions\Action::make('view')
                    ->label('View')
                    ->icon('heroicon-m-eye')
                    ->modalHeading(fn ($record) => $record->reference_no." Sales return")
                    ->modalWidth(MaxWidth::SixExtraLarge)
                    ->modalSubmitAction(false)
                    ->modalContent(fn (PurchaseReturn $record): View => view(
                        'filament.resources/purchase-resource.pages.purchase-return',
                        ['record' => $record],
                    )),
                    Tables\Actions\EditAction::make(),
                    Tables\Actions\DeleteAction::make(),
                    Tables\Actions\Action::make("addpayment")
                    ->label('Add payment')
                    ->icon('heroicon-m-banknotes')
                    ->modalWidth("5xl")
                    ->fillForm(fn ($record) : array  =>[
                        'amount' => $record->amount_due
                    ])
                    ->form([
                        Forms\Components\Section::make('Sales Return Payment Form')
                        ->description('')
                        ->schema([

                         Forms\Components\TextInput::make('amount')
                        ->label('Balance')
                        ->prefix("GHC")
                        ->default(0)
                        ->required(),

                        Forms\Components\DateTimePicker::make('paid_on')
                        ->label('paid on')
                        ->default(0)
                        ->required(), 
                    
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
                    ])
                    ->action(function($data,PurchaseReturn $record){

                         //debit payment account
                        $amount = $data["amount"];
                        $account = PaymentAccount::where('id',$record->account_id)->first();

                        if ($account) {
                            $account->current_balance += $amount;
                            $account->save();
                        }

                        $paymentinsert = [
                            'purchase_return_id' => $record->id,
                            'account_id' => $data["account_id"],
                            'amount' =>$data["amount"],
                            'paid_on' => $data["paid_on"],
                            'cheque_no' => $data["cheque_no"] ?? null,
                            'gift_card_id' => $data["gift_card_id"] ?? null,
                            'paypal_transaction_id' => null,
                            'paying_method' => $data["paying_method"],
                            'payment_note' => $data["payment_note"],
                            'bankname' => $data["bankname"] ?? null,
                            'accountnumber' => $data["accountnumber"] ?? null,
                            'payment_type' => "debit",
                            'payment_ref' => "SPP-".date('Ymd')."-".date('hms'),
                            'paying_type' => "Purchase return",
                            'paid_on' => now(),
                            'balance' => $account->current_balance ?? 0,
                            'user_id' => auth()->user()->id        
                        ];


                        $new = new Payment($paymentinsert);
                        $new->save();

                        $new->customer_id = $record->sale->customer_id;
                        $new->save();

                        $total = (int) $record->grand_total;
                        $paid = (int) $record->amount_paid + (int) $data["amount"];
                        $left = (int) $total - $paid;

                        if ($left > 1) {
                            $record->payment_status = PaymentStatus::Due;
                        }else {
                            $record->payment_status = PaymentStatus::Paid;
                        }

                        $record->amount_paid = $paid;
                        $record->amount_due = $left;
                        $record->save();
                    

                        Notification::make()
                        ->success()
                        ->title('Payment recorded')
                        ->body('Payment recorded successfully!.')
                        ->send();

                    }),

                    Tables\Actions\Action::make("viewpayment")
                    ->label('View payments')
                    ->icon('heroicon-m-banknotes')
                    ->modalWidth('6xl')
                    ->modalSubmitAction(false)
                    ->modalCancelAction(false)
                    ->fillForm(fn (SaleReturn $record) : array => [
                        'payments' => $record->payments
                    ])
                    ->infolist([
                        RepeatableEntry::make('payments')
                        ->label("")
                        ->schema([
                            TextEntry::make("paid_on"),
                            TextEntry::make("amount")
                            ->formatStateUsing(fn (string $state) => "GHC ".$state),
                            TextEntry::make("paying_method"),
                            TextEntry::make("account.account_name"),
                            Actions::make([
                                ActionsAction::make('edit')
                                ->icon('heroicon-m-pencil')
                                ->url(fn ($record) => PaymentResource::getUrl('edit',['record' => $record])),

                                ActionsAction::make('delete')
                                ->color('danger')
                                ->requiresConfirmation()
                                ->icon('heroicon-m-trash')
                                ->before(function($record){
                                    $amount = $record->amount;
                                    $accountid = $record->account_id;

                                    $payment = PurchaseReturn::where('id',$record->purchase_return_id)->first();
                                    $total = $payment->total_amount;
                                    $paid = $payment->amount_paid;
                                    $bal = $payment->amount_due;

                                    $newpaid = $paid - $amount;
                                    $newbalnace = $total - $newpaid;

                                    $payment->amount_paid = $newpaid;
                                    $payment->amount_due = $newbalnace;
                                    
                                    if ($newbalnace > 0) {
                                        $payment->payment_status = PaymentStatus::Partial;
                                    }else {
                                        $payment->payment_status = PaymentStatus::Paid;
                                    }

                                    $payment->save();

                                    //update account.
                                    $paycc = PaymentAccount::where('id',$accountid)->first();
                                    if ($paycc) {
                                        $balance = $paycc->current_balance - $amount;
                                        $paycc->current_balance = $balance;
                                        $paycc->save();
                                    }
                                })
                                ->action(fn ($record) => $record->delete()),
                            ])
                            
                        ])->columns(5)
                    ]),

                ])
            ], position: ActionsPosition::AfterCells)
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                ]),
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
            'index' => Pages\ListPurchaseReturns::route('/'),
            'create' => Pages\CreatePurchaseReturn::route('/create'),
            'edit' => Pages\EditPurchaseReturn::route('/{record}/edit'),
        ];
    }
}
