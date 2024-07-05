<?php

namespace App\Filament\Cashier\Resources;

use App\Filament\Cashier\Resources\SalesResource\Pages;
use App\Filament\Cashier\Resources\SalesResource\RelationManagers;
use App\Livewire\Sound;
use App\Models\Clients;
use App\Models\Coupon;
use App\Models\Product;
use App\Models\Sales;
use App\Models\Taxrates;
use App\Models\Warehouse;
use Filament\Infolists\Components\TextEntry;
use App\Services\Saleservice;
use Filament\Forms;
use Filament\Forms\Form;
use Icetalker\FilamentTableRepeater\Forms\Components\TableRepeater;
use Filament\Resources\Resource;
use Filament\Forms\Components\Actions\Action;
use Filament\Tables\Actions\ActionGroup;
use Filament\Tables;
use Filament\Tables\Table;
use Filament\Notifications\Notification;
use Filament\Support\Enums\MaxWidth;
use Filament\Tables\Columns\Summarizers\Sum;
use Filament\Tables\Enums\ActionsPosition;
use Illuminate\Contracts\View\View;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class SalesResource extends Resource
{
    protected static ?string $model = Sales::class;

    protected static ?string $navigationIcon = 'heroicon-o-rectangle-stack';

   protected static ?string $slug = 'sales/cashier';
    protected static ?string $navigationGroup = 'Sale';
    protected static ?string $navigationLabel = 'Sale';
    protected static ?string $modelLabel = 'Sale';
    protected static ?int $navigationSort = 1;

    public static function getEloquentQuery(): Builder
    {
        return parent::getEloquentQuery()
        ->where('user_id', auth()->user()->id)
        ->orderBy('id','desc');
    }
    
    public static function form(Form $form): Form
    {
        return $form
        ->schema([
            Forms\Components\Section::make('')
            ->description('')
            ->schema([
                Forms\Components\Livewire::make(Sound::class)
                ->columnSpanFull(),
                Forms\Components\DatePicker::make('transaction_date')
                ->placeholder("Transaction date")
                ->label("")
                ->default(date('Y-m-d'))
                ->required(),
                Forms\Components\TextInput::make('reference_number')
                ->label("")
                ->placeholder("Reference number")
                //->default(request()->routeIs('filament.admin.resources.sales.create'))
                ->maxLength(255),
                Forms\Components\Select::make('warehouse_id')
                ->label('')
                ->required()
                ->options(Warehouse::pluck('name','id'))
                ->default(1)
                ->preload()
                ->searchable(),
                Forms\Components\Hidden::make('user_id')
                ->default(auth()->user()->id),
                Forms\Components\Select::make('customer_id')
                ->label('')
                ->options(Clients::where('is_active', true)->pluck('name','id'))
                ->preload()
                ->default(1),
                // Forms\Components\TextInput::make('biller_id')
                //     ->maxLength(255),
                
                Forms\Components\Select::make('scan_code')
                ->label('')
                // ->options(Product::all()->pluck('product_name', 'id'))
                ->getSearchResultsUsing(fn (string $search): array => (new Saleservice())->getproductdetails($search))
                ->preload()
                ->placeholder("Scan / Search product by name / code")
                ->columnSpanFull()
                ->searchable()
                ->dehydrated(false)
                ->live()
                ->afterStateUpdated(function (Forms\Set $set,Forms\Get $get, ?string $state) {
                    $repeaterItems = $get('items');
                    
                    // Flag to check if state exists
                    $stateExists = false;
                    
                    // Loop through the items array
                    foreach ($repeaterItems as $key => $item) {
                        if ($item['product_id'] === $state) {
                            // State exists, update quantity and total price
                            $repeaterItems[$key]['qty'] += 1;
                            $repeaterItems[$key]['total'] = $repeaterItems[$key]['qty'] * $item['unit_price'];
                            $stateExists = true;
                            break; // Exit the loop since the state has been found
                        }
                    }
                    
                    $product = Product::find($state);
                    
                    //check promotional
                    if ($product->promotional_price == true) {
                        //checkdate
                        $today = new \DateTime(); // Current date
                        $start = isset($product->promotion) ? new \DateTime($product->promotion->promotion_start) : null;
                        $end = isset($product->promotion) ? new \DateTime($product->promotion->promotion_end) : null;
                        
                        if ($start && $end) {
                            if ($today >= $start && $today <= $end) {
                                $px = $product->promotion?->promotion_price;
                                //check task method if its exclusive
                                if ($product->tax_method == "1") {
                                    $newpx = ($px + ($product->tax?->rate / 100) * $px);
                                }else{
                                    $newpx = $px;
                                }
                                
                            } elseif ($today < $start) {
                                
                                $px = $product->product_price ?? 0;
                                //check task method if its exclusive
                                if ($product->tax_method == "1") {
                                    $newpx = ($px + ($product->tax?->rate / 100) * $px);
                                }else{
                                    $newpx = $px;
                                }
                                
                            } else {
                                $px = $product->product_price ?? 0;
                                //check task method if its exclusive
                                if ($product->tax_method == "1") {
                                    $newpx = ($px + ($product->tax?->rate / 100) * $px);
                                }else{
                                    $newpx = $px;
                                }
                            }
                        } else {
                            $px = $product->product_price ?? 0;
                            //check task method if its exclusive
                            if ($product->tax_method == "1") {
                                $newpx = ($px + ($product->tax?->rate / 100) * $px);
                            }else{
                                $newpx = $px;
                            }
                        }
                    }else {
                        $px = $product->product_price ?? 0;
                        //check task method if its exclusive
                        if ($product->tax_method == "1") {
                            $newpx = ($px + ($product->tax?->rate / 100) * $px);
                        }else{
                            $newpx = $px;
                        }
                    }
                    
                    $px = $newpx;
                    
                    $qty = 1;
                    $total = $px * $qty;

                    $data = [
                        'product_name' =>  $product->product_name." in-stock: ".$product->product_qty,
                        'product_id' => $state,
                        'unit_price' => $px,
                        'qty' => 1,
                        'total' => $total,
                        'sale_unit_id' => $product->sale_unit_id,
                        'discount' => 0,
                        'tax_rate' => $product->tax?->rate ?? 0,
                        'tax' => $product->tax_method == "1" ? (($product->tax?->rate / 100) * $px) : 0,
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
                    
                    //self::calculateitemsstate($state,$set,$get);
                    self::updateTotals($get,$set);
                    
                })
                ->deleteAction(
                    fn(Action $action) => $action->after(fn(Forms\Get $get, Forms\Set $set) => self::updateTotals($get, $set)),
                    )
                    ->reorderable(false)
                    ->relationship('saleitem')
                    ->schema([
                        Forms\Components\Hidden::make('product_id'),

                        Forms\Components\Hidden::make('sale_unit_id'),
                        Forms\Components\Hidden::make('discount'),
                        Forms\Components\Hidden::make('tax_rate'),
                        Forms\Components\Hidden::make('tax'),

                        // ->label('Product')
                        // ->options(Product::all()->pluck('product_name', 'id'))
                        // ->searchable()
                        // ->preload()
                        // ->disableOptionsWhenSelectedInSiblingRepeaterItems()
                        // ->required(),

                        Forms\Components\TextInput::make('product_name')
                        ->disabled(),

                        // Forms\Components\Select::make('product_id')
                        // ->label('Product')
                        //  ->options(Product::all()->pluck('product_name', 'id'))
                        //  ->searchable()
                        //  ->preload()
                        // ->visible(fn (string $operation): bool => $operation === 'edit')
                        // ->disabled(),
                        
                        Forms\Components\TextInput::make('unit_price')
                        ->type("number")
                        ->readOnly()
                        ->label('Price')
                        ->required(),
                        
                        Forms\Components\TextInput::make('qty')
                        ->integer()
                        ->default(1)
                        ->live()
                        ->afterStateHydrated(function (Forms\Get $get,Forms\Set $set, $state){
                            $tot = $state * $get('unit_price');
                            $set("total",$tot);
                            self::updateTotals($get,$set);
                        })
                        ->required(),
                        
                        Forms\Components\Placeholder::make('ptotal')
                        ->content(function ($get,$set){
                            $set("total",$get("unit_price") * $get("qty"));
                            return $get("unit_price") * $get("qty");
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
                        
                        
                        Forms\Components\Section::make('')
                        ->schema([
                            
                            Forms\Components\Placeholder::make('item')
                            ->columnSpanFull()
                            ->content(function ($get,$set){
                                
                                return "Items: ".$get("item")."(".$get("total_qty").")";
                            })
                            ->label(''),
                                    
                                    Forms\Components\Hidden::make('coupon_type')
                                    ->dehydrated(false),
                                    
                                    Forms\Components\TextInput::make('coupon_discount')
                                    ->label("")
                                    ->default(0)
                                    ->readOnly()
                                    ->hintAction(
                                        Action::make("coupon")
                                        ->icon('heroicon-m-pencil-square')
                                        ->label("Add Coupon")
                                        ->modalSubmitActionLabel('Check Coupon')
                                        ->form([                                                                                
                                            Forms\Components\TextInput::make('coupon_value')
                                            ->label('Enter code')
                                            ->required(),
                                            
                                            ])
                                            ->action(function(array $data, Forms\Set $set, Forms\Get $get){
                                                
                                                $coupon = Coupon::where('code',$data['coupon_value'])
                                                ->where('is_active', true)
                                                ->first();
                                                
                                                $todate = new \DateTime(); // Current date
                                                $expiry_date = new \DateTime($coupon->expiry_date);
                                                
                                                if(!$coupon){
                                                    
                                                    Notification::make()
                                                    ->title('Invalid coupon code!')
                                                    ->warning()
                                                    ->send();
                                                }else{
                                                    $px = $coupon->amount;
                                                    
                                                    if($todate > $expiry_date){
                                                        
                                                        Notification::make()
                                                        ->title('This Coupon has expired!')
                                                        ->warning()
                                                        ->send();
                                                        
                                                    }elseif($coupon->qty <= $coupon->used){
                                                        
                                                        Notification::make()
                                                        ->title('This Coupon is no longer available!')
                                                        ->warning()
                                                        ->send();
                                                        
                                                    }elseif($coupon->coupon_type == "Flat"){
                                                        $set("coupon_id",$coupon->id);
                                                        $set("coupon_type","Flat");
                                                        $set("coupon_discount", $px);
                                                        
                                                        Notification::make()
                                                        ->title("Congratulation! You got ".$px." discount")
                                                        ->success()
                                                        ->send();
                                                        
                                                    }elseif ($coupon->coupon_type == "Discount") {
                                                        $set("coupon_id",$coupon->id);
                                                        $set("coupon_type","Discount");
                                                        $set("coupon_discount", $px);
                                                        
                                                        Notification::make()
                                                        ->title("Congratulation! You got ".$px."% discount")
                                                        ->success()
                                                        ->send();
                                                    }
                                                }
                                                
                                                self::updateTotals($get, $set);
                                                
                                            })
                                        ),
                                        
                                        // Forms\Components\Placeholder::make('coupon_discount')
                                        // ->content(function ($get,$set){
                                            
                                            //     return $get("coupon_discount");
                                            // })
                                            // ->label(''),
                                            
                                            Forms\Components\Hidden::make('order_tax')
                                            ->default(0),
                                            
                                            
                                            Forms\Components\TextInput::make('order_tax_rate')
                                            ->label("")
                                            ->default(0)
                                            ->readOnly()
                                            ->hintAction(
                                                Action::make("Tax")
                                                ->icon('heroicon-m-pencil-square')
                                                ->label("Add Tax")
                                                ->modalSubmitActionLabel('Add Tax')
                                                ->form([
                                                    Forms\Components\Select::make('order_tax')
                                                    ->label('')
                                                    ->options(Taxrates::pluck('name','id'))
                                                    ->searchable()
                                                    ->required()
                                                    
                                                    ])
                                                    ->action(function(array $data, Forms\Set $set, Forms\Get $get){
                                                        //$set("order_tax", $data["discount_type"]);
                                                        $tax = Taxrates::find($data["order_tax"]);
                                                        
                                                        $rate = $tax->rate;
                                                        $set("order_tax_rate",$rate);
                                                        self::updateTotals($get, $set);
                                                    })
                                                ),
                                                
                                                Forms\Components\TextInput::make('shipping_cost')
                                                ->numeric()
                                                ->live(true)
                                                ->afterStateUpdated(function (Forms\Get $get, Forms\Set $set) {
                                                    self::updateTotals($get, $set);
                                                })
                                                ->default(0),
                                                
                                                Forms\Components\Hidden::make('order_discount_type')
                                                ->default(""),
                                                
                                                Forms\Components\TextInput::make('order_discount_value')
                                                ->label("")
                                                ->default(0)
                                                ->readOnly()
                                                ->hintAction(
                                                    Action::make("Discount")
                                                    ->icon('heroicon-m-pencil-square')
                                                    ->label("Add Discount")
                                                    ->modalSubmitActionLabel('Add Discount')
                                                    ->form([
                                                        Forms\Components\Select::make('discount_type')
                                                        ->label('Discount Type')
                                                        ->options([
                                                            'Flat' => 'Flat',
                                                            'Discount' => 'Discount'
                                                            ])
                                                            ->searchable()
                                                            ->required(),
                                                            
                                                            Forms\Components\TextInput::make('value')
                                                            ->label('Value')
                                                            ->required(),
                                                            
                                                            ])
                                                            ->action(function(array $data, Forms\Set $set, Forms\Get $get){
                                                                $set("order_discount_type", $data["discount_type"]);
                                                                $set("order_discount_value", $data["value"]);
                                                                self::updateTotals($get, $set);
                                                            })
                                                        ),
                                                        
                                                        
                                                        Forms\Components\TextInput::make('total_price')
                                                        ->label('Sub Total')
                                                        ->readOnly()
                                                        ->default(0),
                                                        
                                                        
                                                        Forms\Components\TextInput::make('grand_total')
                                                        ->label('Grand Total')
                                                        ->readOnly()
                                                        ->default(0),
                                                        
                                                        
                                                        ])
                                                        ->columns(6),
                                                        
                                                        
                                                        Forms\Components\Hidden::make('item')
                                                        ->default(0),
                                                        Forms\Components\Hidden::make('total_qty')
                                                        ->default(0),
                                                        Forms\Components\Hidden::make('currency_id'),
                                                        Forms\Components\Hidden::make('coupon_id'),
                                                        Forms\Components\Hidden::make('paid_amount')
                                                        ->default(0),
                                                        Forms\Components\Hidden::make('total_discount')
                                                        ->default(0),
                                                        Forms\Components\Hidden::make('sale_note'),
                                                        Forms\Components\Hidden::make('staff_note'),

                                                        //payments
                                                        Forms\Components\Hidden::make('payment.user_id')
                                                        ->default(auth()->user()->id),
                                                        Forms\Components\Hidden::make('payment.amount')
                                                        ->default(0),
                                                        Forms\Components\Hidden::make('payment.change')
                                                        ->default(0),
                                                        Forms\Components\Hidden::make('payment.customer_id'),
                                                        Forms\Components\Hidden::make('payment.paying_method'),
                                                        Forms\Components\Hidden::make('payment.payment_note')

                                                        ])
                                                        ->columns(4),
                                                    ]);
                                                }
                                                
public static function table(Table $table): Table
{
return $table
->recordUrl("")
->columns([
Tables\Columns\TextColumn::make('transaction_date')
->label('Date')
->date()
->sortable(),
Tables\Columns\TextColumn::make('sales_type')
->label('Sales Type')
->searchable(),
Tables\Columns\TextColumn::make('reference_number')
->label('Reference')
->searchable(),
Tables\Columns\TextColumn::make('user_id')
->hidden()
->searchable(),
Tables\Columns\TextColumn::make('customer.name')
->searchable(),
Tables\Columns\TextColumn::make('biller.name')
->label('Biller')
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
->sortable()
->summarize(Sum::make()->money('GHC')->label('Total sales')),

Tables\Columns\TextColumn::make('currency_id')
->numeric()
->sortable()
->toggleable(isToggledHiddenByDefault: true),
Tables\Columns\TextColumn::make('order_tax_rate')
->searchable()
->toggleable(isToggledHiddenByDefault: true),
Tables\Columns\TextColumn::make('order_tax')
->searchable()
->toggleable(isToggledHiddenByDefault: true),
Tables\Columns\TextColumn::make('order_discount_type')
->searchable()
->toggleable(isToggledHiddenByDefault: true),
Tables\Columns\TextColumn::make('order_discount_value')
->numeric()
->sortable()
->toggleable(isToggledHiddenByDefault: true),
Tables\Columns\TextColumn::make('coupon_id')
->numeric()
->sortable()
->toggleable(isToggledHiddenByDefault: true),
Tables\Columns\TextColumn::make('coupon_discount')
->numeric()
->sortable()
->toggleable(isToggledHiddenByDefault: true),
Tables\Columns\TextColumn::make('shipping_cost')
->numeric()
->sortable()
->toggleable(isToggledHiddenByDefault: true),
Tables\Columns\TextColumn::make('paid_amount')
->formatStateUsing(fn (string $state): string => "GHC ".number_format($state,2))
->sortable()
->summarize(Sum::make()->money('GHC')->label('Total payments')),

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
    
    Tables\Actions\EditAction::make(),

    //Tables\Actions\ViewAction::make(),

    Tables\Actions\Action::make('view')
        ->label('View')
        ->icon('heroicon-m-eye')
        ->modalHeading(fn ($record) => $record->reference_number." Sales")
        ->modalWidth(MaxWidth::SixExtraLarge)
        ->modalSubmitAction(false)
        ->modalContent(fn (Sales $record): View => view(
            'filament.resources/sales-resource.pages.view-sale',
            ['record' => $record],
    )),

    Tables\Actions\Action::make('Print Invoice')
    ->icon('heroicon-m-receipt-percent')
    ->color('success')
    ->url( fn ($record) => route('pos-invoice', $record->id), shouldOpenInNewTab: true),




    ])
    
    
    ], position: ActionsPosition::BeforeCells)
    ->bulkActions([
        Tables\Actions\BulkActionGroup::make([
            Tables\Actions\DeleteBulkAction::make(),
        ]),
    ]);
}
                                                            
                                                            public static function getPages(): array
                                                            {
                                                                return [
                                                                    'index' => Pages\ListSales::route('/'),
                                                                    'create' => Pages\CreateSales::route('/create'),
                                                                    'edit' => Pages\EditSales::route('/{record}/edit'),
                                                                ];
                                                            }
                                                            
                                                            public static function calculateitems($set,$get) {
                                                                
                                                                $state = $get('items');
                                                                
                                                                $totitem = count($state);
                                                                $set("item",$totitem);
                                                                
                                                                $totalqty = collect($state)
                                                                ->pluck('qty')
                                                                ->sum();
                                                                
                                                                $set("total_qty",$totalqty);
                                                                
                                                            }
                                                            
                                                            public static function calculateitemsstate($state, $set,$get) {
                                                                
                                                                $totitem = count($state);
                                                                $set("item",$totitem);
                                                                
                                                                $totalqty = collect($state)
                                                                ->pluck('qty')
                                                                ->sum();
                                                                
                                                                $set("total_qty",$totalqty);
                                                                
                                                                // $total = collect($state)
                                                                // ->pluck('total')
                                                                // ->sum();
                                                                
                                                                // $set("total_price",$total);
                                                                
                                                                // static::calculateitems($set,$get);
                                                                
                                                            }
                                                            
                                                            public static function setquantity($productid, $state, $set,$get) {
                                                                
                                                                static::calculateitems($set,$get);
                                                            }
                                                            
                                                            // This function updates totals based on the selected products and quantities
                                                            public static function updateTotals(Forms\Get $get, Forms\Set $set): void
                                                            {
                                                                // Retrieve all selected products and remove empty rows
                                                                $selectedProducts = collect($get('items'))->filter(fn($item) => !empty($item['product_id']) && !empty($item['qty']));
                                                                
                                                                $qty = $selectedProducts->pluck('qty')->sum();
                                                                $set("total_qty",$qty);
                                                                
                                                                // Retrieve prices for all selected products
                                                                
                                                                // $prices = Product::find($selectedProducts->pluck('product_id'))
                                                                // ->pluck('product_price', 'id');
                                                                
                                                                // Calculate the new price for each selected product
                                                                $prices = [];
                                                                
                                                                foreach ($selectedProducts as $selectedProduct) {
                                                                    $product = Product::find($selectedProduct['product_id']);
                                                                    
                                                                    if ($product) {
                                                                        $px = $product->product_price;
                                                                        $newpx = $px; // By default, set new price as the original price
                                                                        
                                                                        // Check if the product has a promotion and if it's active
                                                                        if ($product->promotional_price == true) {
                                                                            $today = new \DateTime(); // Current date
                                                                            $start = isset($product->promotion) ? new \DateTime($product->promotion->promotion_start) : null;
                                                                            $end = isset($product->promotion) ? new \DateTime($product->promotion->promotion_end) : null;
                                                                            
                                                                            if ($start && $end) {
                                                                                if ($today >= $start && $today <= $end) {
                                                                                    $newpx = $product->promotion?->promotion_price;
                                                                                } elseif ($today < $start) {
                                                                                    $newpx = $product->product_price ?? 0;
                                                                                } else {
                                                                                    $newpx = $product->product_price ?? 0;
                                                                                }
                                                                            } else {
                                                                                $newpx = $product->product_price ?? 0;
                                                                                
                                                                            }
                                                                        }
                                                                        
                                                                        // Check if tax is applicable
                                                                        if ($product->tax_method == "1") {
                                                                            $newpx += ($product->tax ? $product->tax->rate / 100 * $newpx : 0);
                                                                        }
                                                                        
                                                                        // Store the calculated price for the product
                                                                        $prices[$selectedProduct['product_id']] = $newpx;
                                                                    }
                                                                }
                                                                
                                                                
                                                                // Calculate subtotal based on the selected products and quantities
                                                                $subtotal = $selectedProducts->reduce(function ($subtotal, $product) use ($prices) {
                                                                    return $subtotal + ($prices[$product['product_id']] * $product['qty']);
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
                                                                
                                                                $set("order_tax",$tax);
                                                                
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
                                                            }
                                                        }
                                                        