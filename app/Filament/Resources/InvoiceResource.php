<?php

namespace App\Filament\Resources;

use App\Filament\Resources\InvoiceResource\Pages;
use App\Filament\Resources\InvoiceResource\RelationManagers;
use App\Models\ClientGroup;
use App\Models\Clients;
use App\Models\Invoice;
use App\Models\Product;
use App\Models\Taxrates;
use App\Services\Saleservice;
use Filament\Forms;
use Filament\Forms\Components\Wizard\Step;
use Filament\Forms\Form;
use Filament\Notifications\Notification;
use Filament\Resources\Resource;
use Filament\Tables\Enums\ActionsPosition;
use Filament\Tables;
use Filament\Tables\Table;
use Icetalker\FilamentTableRepeater\Forms\Components\TableRepeater;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use Filament\Forms\Components\Actions\Action;
use Filament\Tables\Actions\ActionGroup;

class InvoiceResource extends Resource
{
    protected static ?string $model = Invoice::class;

    protected static ?string $navigationIcon = 'heroicon-o-rectangle-stack';
    protected static ?string $slug = 'quotations';
    protected static ?string $navigationGroup = 'Sale';
    protected static ?string $navigationLabel = 'Quotations';
    protected static ?string $modelLabel = 'Quotation';
    protected static ?int $navigationSort = 5;

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

                        Forms\Components\Wizard::make([

                            Forms\Components\Wizard\Step::make('Customer')
                                ->schema([

                                Forms\Components\Section::make('')
                                    ->description('')
                                    ->schema([
                                        Forms\Components\Select::make('client_id')
                                        ->label('Client name')
                                          ->options(Clients::all()->pluck('name', 'id'))
                                          ->searchable()
                                          ->preload()
                                          ->required()
                                          ->createOptionForm([
                                            Forms\Components\Section::make('')
                                            ->description('')
                                            ->schema([
                                                    Forms\Components\Select::make('client_group_id')
                                                    ->label('Customer group')
                                                    ->options(ClientGroup::where('is_active',true)->pluck('name','id'))
                                                    ->preload()
                                                    ->required()
                                                    ->searchable(),
                                                    Forms\Components\Hidden::make('user_id')
                                                    ->default(auth()->user()->id),
                                                    Forms\Components\TextInput::make('name')
                                                    ->required()
                                                    ->maxLength(255),
                                                    Forms\Components\TextInput::make('company_name')
                                                    ->maxLength(255),
                                                    Forms\Components\TextInput::make('email')
                                                    ->email()
                                                    ->maxLength(255),
                                                    Forms\Components\TextInput::make('phone_number')
                                                    ->tel()
                                                    ->required()
                                                    ->maxLength(255),
                                                    Forms\Components\TextInput::make('tax_no')
                                                    ->maxLength(255),
                                                    Forms\Components\Textarea::make('address')
                                                    ->required()
                                                    ->maxLength(65535)
                                                    ->columnSpanFull(),
                                                    Forms\Components\TextInput::make('city')
                                                    ->required()
                                                    ->maxLength(255),
                                                    Forms\Components\TextInput::make('state')
                                                    ->maxLength(255),
                                                    Forms\Components\TextInput::make('postal_code')
                                                    ->maxLength(255),
                                                    Forms\Components\TextInput::make('country')
                                                    ->maxLength(255),
                                                    Forms\Components\Hidden::make('is_active')
                                                    ->default(1),
                                                ])
                                                ->columns(3),
                                            ]),

                                    Forms\Components\Textarea::make('description')
                                        ->maxLength(65535)
                                        ->columnSpanFull(),
                                        
                                ])->columns(2),

                            ]),

                            Forms\Components\Wizard\Step::make('Invoice Items')
                            ->schema([

                                Forms\Components\Section::make('')
                                    ->description('')
                                    ->schema([                                    
                                    
                                        Forms\Components\Select::make('scan_code')
                                        ->label('')
                                        // ->options(Product::all()->pluck('product_name', 'id'))
                                        ->getSearchResultsUsing(fn (string $search, $get): array => (new Saleservice())->getadjustmentproduct($search,$get('warehouse_id')))
                                        ->preload()
                                        ->placeholder("Scan / Search product by name / code")
                                        ->columnSpanFull()
                                        ->searchable()
                                        ->dehydrated(false)
                                        ->live(debounce: 500)
                                        ->afterStateUpdated(function (Forms\Set $set,Forms\Get $get, ?string $state) {
                                            $repeaterItems = $get('items');
                                            
                                            $state = explode(",",$state);
                                            
                                            $product = Product::find($state[0]);
                                            
                                            // Flag to check if state exists
                                            $stateExists = false;
                                            
                                            $product_type = $product->product_type;
                                            
                                            // Loop through the items array
                                            foreach ($repeaterItems as $key => $item) {
                                                if ($product_type == "Single" && $item['product_id'] === $state[0]  && $item['warehouse_id'] == $get('warehouse_id')) {
                                                    
                                                    $currentqty = (int) $repeaterItems[$key]['qty'] + 1;
                                                    $stock = (int) $repeaterItems[$key]['stock'];
                                                    $tax_rate = (int) $repeaterItems[$key]['tax_rate'];
                                                    
                                                    if($currentqty > $stock){
                                                        
                                                        Notification::make()
                                                        ->title($product->product_name. ' is out of stock!')
                                                        ->warning()
                                                        ->send();
                                                        
                                                        return;
                                                    }
                                                    
                                                    $repeaterItems[$key]['qty'] += 1;
                                                    $total = $repeaterItems[$key]['qty'] * $item['unit_price'];
                                                    $repeaterItems[$key]['total'] = ($total + (($tax_rate / 100) * $total));
                                                    $repeaterItems[$key]['tax'] =  ($tax_rate / 100 * ($repeaterItems[$key]['qty'] * $item['unit_price']));
                                                    $stateExists = true;
                                                    break; // Exit the loop since the state has been found
                                                    
                                                }elseif ($product_type == "Variation" && $item['product_id'] === $state[0] && $item['variant_id'] === $state[1] && $item['warehouse_id'] == $get('warehouse_id')) {
                                                    
                                                    $currentqty = (int) $repeaterItems[$key]['qty'] + 1;
                                                    $stock = (int) $repeaterItems[$key]['stock'];
                                                    $tax_rate = (int) $repeaterItems[$key]['tax_rate'];
                                                    
                                                    if($currentqty > $stock){
                                                        
                                                        Notification::make()
                                                        ->title($product->product_name. 'is out of stock!')
                                                        ->warning()
                                                        ->send();
                                                        
                                                        return;
                                                    }
                                                    
                                                    $repeaterItems[$key]['qty'] += 1;
                                                    $total = $repeaterItems[$key]['qty'] * $item['unit_price'];
                                                    $repeaterItems[$key]['total'] = ($total + (($tax_rate / 100) * $total));
                                                    $repeaterItems[$key]['tax'] =  ($tax_rate / 100 * ($repeaterItems[$key]['qty'] * $item['unit_price']));
                                                    $stateExists = true;
                                                    break; // Exit the loop since the state has been found
                                                }
                                            }
                                            
                                            $promo = $product->promotions()
                                            ->when($product_type == "Single",function($query) use($get){
                                                return $query->where('warehouse_id',$get('warehouse_id'));
                                            })
                                            ->when($product_type == "Variation",function($query) use($state,$get){
                                                return $query->where('warehouse_id',$get('warehouse_id'))
                                                ->where('variant_id',$state[1]);
                                            })
                                            ->when($product_type == "Combo",function($query) use($state,$get){
                                                return $query->where('warehouse_id',$get('warehouse_id'))
                                                ->where('variant_id',$state[1]);
                                            })->activepromo()
                                            ->currentdate()->first();
                                            
                                            
                                            if ($product_type == "Single") {
                                                
                                                $product_variant_data = $product->inventory()
                                                ->where('warehouse_id',$get('warehouse_id'))
                                                ->first();
                                                
                                                $stock = $product_variant_data->qty;
                                                
                                                $px = $product_variant_data->selling_price;
                                                
                                            }elseif ($product_type == "Variation") {
                                                
                                                $product_variant_data = $product->inventory()
                                                ->where('variant_id',$state[1])
                                                ->where('warehouse_id',$get('warehouse_id'))
                                                ->first();
                                                
                                                $stock = $product_variant_data->qty;
                                                $itemcode = $product_variant_data->variant?->item_code ?? null;
                                                $px = $product_variant_data->selling_price;
                                                
                                            }
                                            
                                            //check promotional
                                            if ($promo) {
                                                $px = $promo->promotion_price ?? 0;
                                            }
                                            
                                            $tax = Taxrates::whereIn("id",array_values($product->taxes()->pluck('tax_id')->toArray()))->sum('rate');
                                            
                                            //check task method if its exclusive
                                            if ($product->tax_method == "1") {
                                                $newpx = ($px + ($tax / 100) * $px);
                                            }else{
                                                $newpx = $px;
                                            }
                                            
                                            $px = $newpx;
                                            
                                            if ($stock < 1) {
                                                
                                                //$this->dispatch('play-sound');
                                                
                                                Notification::make()
                                                ->title($product->product_name. ' is out of stock!')
                                                ->warning()
                                                ->send();
                                                
                                                $set("scan_code","");
                                                
                                                return;
                                            }
                                            
                                            $qty = 1;
                                            $total = $px * $qty;
                                            
                                            $data = [
                                                'product_name' =>  $product_type == "Variation" ? ($product->product_name."(".$itemcode.")") : ($product->product_name),
                                                'product_id' => $state[0],
                                                'variant_id' => $state[1] ?? null,
                                                'unit_price' => $px,
                                                'qty' => 1,
                                                'total' => $total,
                                                'sale_unit_id' => $product->sale_unit_id,
                                                'discount' => 0,
                                                'tax_rate' => $tax ?? 0,
                                                'tax' => $product->tax_method == "1" ? (($tax / 100) * $px) : 0,
                                                'stock' => $stock,
                                                'warehouse_id' => $get('warehouse_id')
                                            ];
                                            
                                            // If state doesn't exist, add it to the array
                                            if (!$stateExists) {
                                                array_push($repeaterItems, $data);
                                            }
                                            
                                            $set('items', $repeaterItems);
                                            $set("scan_code","");
                                            static::calculateitems($set,$get);
                                            self::updateTotals($get,$set);
                                        }),
                                        
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
                                            ->relationship('orderitems')
                                            ->schema([
                                                Forms\Components\Hidden::make('product_id'),
                                                Forms\Components\Hidden::make('variant_id'),
                                                Forms\Components\Hidden::make('sale_unit_id'),
                                                Forms\Components\Hidden::make('stock')
                                                ->dehydrated(false),
                                                
                                                Forms\Components\TextInput::make('product_name')
                                                ->columnSpan(2),
                                                
                                                Forms\Components\TextInput::make('unit_price')
                                                ->type("number")
                                                ->readOnly()
                                                ->label('Price')
                                                ->required(),
                                                
                                                
                                                
                                                Forms\Components\TextInput::make('qty')
                                                ->integer()
                                                ->default(1)
                                                ->live(onBlur: true)
                                                ->afterStateUpdated(function (Forms\Get $get,Forms\Set $set, $state,$livewire){
                                                    $tot = $state * $get('unit_price');
                                                    
                                                    $pxqty = $get("stock");
                                                    
                                                    if ($state > $pxqty) {
                                                        $livewire->dispatch('play-error');
                                                        $set("qty",$state - 1);
                                                        static::calculateitems($set,$get);
                                                        return;
                                                        
                                                    }elseif($state < 0){
                                                        $livewire->dispatch('play-error');
                                                        $set("qty",0);
                                                        static::calculateitems($set,$get);
                                                        return;
                                                    }
                                                    
                                                    
                                                    $set("tax",(($get("tax_rate") / 100) * $tot));
                                                    $set("total",($tot + ($get("tax_rate") / 100) * $tot));                            
                                                    //self::updateTotals($get,$set);
                                                })
                                                ->afterStateHydrated(function (Forms\Get $get,Forms\Set $set, $state){
                                                    $tot = $state * $get('unit_price');
                                                    
                                                    
                                                    $set("tax",(($get("tax_rate") / 100) * $tot));
                                                    $set("total",($tot + ($get("tax_rate") / 100) * $tot));                            
                                                    //self::updateTotals($get,$set);
                                                })
                                                ->required(),
                                                
                                                
                                                Forms\Components\Hidden::make('discount'),
                                                Forms\Components\TextInput::make('tax_rate'),
                                                Forms\Components\TextInput::make('tax'),
                                                
                                                
                                                Forms\Components\Placeholder::make('ptotal')
                                                ->content(function ($get,$set){
                                                    $tot = $get("qty") * $get('unit_price');
                                                    
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
                                                ->columnSpan('full'),
                                                
                                                Forms\Components\Placeholder::make('totalitem')
                                                ->content(function ($get,$set){
                                                    
                                                    return "Total item: ".$get("item");
                                                })
                                                ->label(''),
                                                
                                                Forms\Components\Placeholder::make('totqty')
                                                ->content(function ($get,$set){
                                                    
                                                    return "Total qty: ".$get("total_qty");
                                                })
                                                ->label(''),
                                                
                                                
                                                Forms\Components\Placeholder::make('tottax')
                                                ->content(function ($get,$set){
                                                    
                                                    return "Total tax: ".number_format($get("tottax"),2);
                                                })
                                                ->label(''),
                                                
                                                Forms\Components\Placeholder::make('item')
                                                ->content(function ($get,$set){
                                                    
                                                    return "Subtotal: ".number_format($get("total_price"),2);
                                                })
                                                ->label(''),
                                        
                                ])->columnSpanFull(),

                            ]),


                            Forms\Components\Wizard\Step::make('Initial Payment')
                            ->schema([

                                Forms\Components\Section::make('')
                                    ->description('')
                                    ->schema([

                                    Forms\Components\TextInput::make('amount_due')
                                        ->numeric(),

                                    Forms\Components\TextInput::make('amount_paid')
                                    ->default(0)
                                    ->numeric(),
                                        
                                ])->columns(2),

                            ]),


                            Forms\Components\Wizard\Step::make('invoice Terms')
                            ->schema([

                                Forms\Components\Section::make('')
                                    ->description('')
                                    ->schema([

                                    Forms\Components\Textarea::make('terms')
                                        ->maxLength(65535)
                                        ->columnSpanFull(),

                                    Forms\Components\DatePicker::make('issue_date'),
                                    Forms\Components\DatePicker::make('due_date'),
                                        
                                ])->columns(2),

                            ]),


                        ])
                        
                ]),
                
               // Forms\Components\Hidden::make('sent')
                    //->required(),
                
                //Forms\Components\Hidden::make('fully_paid_at'),
            ]);
    }

     // This function updates totals based on the selected products and quantities
    public static function updateTotals(Forms\Get $get, Forms\Set $set): void
    {                
        // Retrieve all selected products and remove empty rows
        $selectedProducts = collect($get('items'))->filter(fn($item) => !empty($item['product_id']) && !empty($item['qty']));

        $payments = collect($get('Payment'));

        $paymentsmade = [];
        
        foreach ($payments as $payment) {
            $paymentsmade["amount"] = $payment['amount'];
        }

        $totalpayment = $payments->reduce(function ($totalpayment, $payment) use ($paymentsmade) {
            return $totalpayment + $paymentsmade[$payment['amount']];
        }, 0);
        
        $qty = $selectedProducts->pluck('qty')->sum();
        $set("total_qty",$qty);
        
        $prices = [];
        
        foreach ($selectedProducts as $selectedProduct) {
            
            $tot = $selectedProduct["qty"] * $selectedProduct['unit_price'];
            
            $total = ($tot + ($selectedProduct["tax_rate"] / 100) * $tot);
            
            $prices[$selectedProduct['product_id']] = $total;
            
        }
        
        
        // Calculate subtotal based on the selected products and quantities
        $subtotal = $selectedProducts->reduce(function ($subtotal, $product) use ($prices) {
            return $subtotal + $prices[$product['product_id']];
        }, 0);
        
        
        //substract coupon
        $coupon_type = $get("coupon_type");
        
        if($coupon_type == "Flat"){
            $coupon_discount = $get("coupon_discount");
        }elseif($coupon_type == "Discount"){
            $coupon_discount = ($get("coupon_discount") / 100) * $subtotal;
        }else{
            $coupon_discount = $get("coupon_discount");
        }
        
        //add tax
        $tax = ($get("order_tax_rate") / 100 ) * $subtotal;
        
        $set("order_tax_value",$tax);
        
        //add shipping cost
        $shipping = $get("shipping_cost");
        
        //substract discount
        $order_discount_type = $get("order_discount_type");
        if($order_discount_type == "Flat"){
            $discount = $get("order_discount_value");
        }elseif($order_discount_type == "Discount"){
            $discount = ($get("order_discount_value") / 100) * $subtotal;
        }else{
            $discount = $get("order_discount_value");
        }
        
        $set("total_discount",$discount);
        
        $grandtotal = ($subtotal + $tax + $shipping) -  ($coupon_discount + $discount);
        
        // Update the state with the new values
        $set('total_price', number_format($subtotal, 2, '.', ''));
        $set('grand_total', number_format($grandtotal, 2, '.', ''));
        
        // $set('payment.grand_total', number_format($grandtotal, 2, '.', ''));
        
        static::calculateitems($set,$get);
    } 

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('due_date')
                ->date()
                ->sortable(),
                
                Tables\Columns\TextColumn::make('reference')
                ->label('Reference')
                ->searchable(),

                Tables\Columns\TextColumn::make('client.name')
                ->default("Sent")
                ->badge()
                ->color('info')
                ->searchable(),

                Tables\Columns\TextColumn::make('quotation_status')
                ->searchable(),               

                Tables\Columns\TextColumn::make('total_qty')
                ->label('Total Products')
                ->numeric()
                ->badge()
                ->sortable(),

                Tables\Columns\TextColumn::make('grand_total')
                ->formatStateUsing(fn (string $state): string => "GHC ".number_format($state,2))
                ->sortable()
                ->badge(),               

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
                Tables\Actions\Action::make('Send')
                ->icon('heroicon-m-envelope')
                ->url(fn (Invoice $record): string => route('send-invoice',['record' => $record]))
                ->openUrlInNewTab(),

                Tables\Actions\Action::make('Download')
                ->icon('heroicon-m-arrow-down-tray')
                ->url(fn (Invoice $record): string => route('download-invoice',['record' => $record]))
                ->openUrlInNewTab(),

                Tables\Actions\ViewAction::make(),

                Tables\Actions\EditAction::make(),

                Tables\Actions\DeleteAction::make()
             ])
            ],position: ActionsPosition::BeforeColumns)
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
            'index' => Pages\ListInvoices::route('/'),
            'create' => Pages\CreateInvoice::route('/create'),
            'view' => Pages\ViewInvoice::route('/{record}'),
            'edit' => Pages\EditInvoice::route('/{record}/edit'),
        ];
    }
}
