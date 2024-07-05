<?php

namespace App\Filament\Resources;

use App\Filament\Resources\PurchaseResource\Pages;
use App\Filament\Resources\PurchaseResource\RelationManagers;
use App\Models\Giftcard;
use App\Models\PaymentAccount;
use App\Models\Product;
use App\Models\Purchase;
use App\Models\Supplier;
use App\Models\Taxrates;
use App\Models\Warehouse;
use App\Partials\Enums\PurchaseStatus;
use App\Services\Saleservice;
use Filament\Forms;
use Filament\Forms\Components\Actions\Action;
use Filament\Forms\Form;
use Filament\Notifications\Notification;
use Filament\Resources\Resource;
use Filament\Support\Enums\MaxWidth;
use Filament\Tables;
use Filament\Tables\Actions\ActionGroup;
use Filament\Tables\Columns\Summarizers\Sum;
use Filament\Tables\Enums\ActionsPosition;
use Filament\Tables\Table;
use Icetalker\FilamentTableRepeater\Forms\Components\TableRepeater;
use Illuminate\Contracts\View\View;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class PurchaseResource extends Resource
{
    protected static ?string $model = Purchase::class;
    
    protected static ?string $navigationIcon = 'heroicon-o-circle-stack';
    protected static ?string $slug = 'purcheses';
    protected static ?string $navigationGroup = 'Purchases';
    protected static ?string $navigationLabel = 'List purchases';
    protected static ?string $modelLabel = 'Purchases';
    protected static ?int $navigationSort = 1;
    
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
                Forms\Components\Select::make('suppier_id')
                ->label('Supplier')
                ->relationship('suplier')
                ->options(Supplier::get()->pluck('fullname','id'))
                ->preload()
                ->searchable()
                ->required()
                ->createOptionForm([
                    Forms\Components\Section::make('')
                    ->description('')
                    ->schema([
                        Forms\Components\Select::make('contact_type')
                        ->options([
                            'Individual' => 'Individual',
                            'Business' => 'Business',
                            ])
                            ->searchable()
                            ->live()
                            ->required(),
                            
                            Forms\Components\TextInput::make('business_name')
                            ->visible(fn (Forms\Get $get) => $get("contact_type") == "Business")
                            ->maxLength(255),
                            
                            Forms\Components\TextInput::make('contact')
                            ->tel()
                            ->required(),
                            
                            Forms\Components\TextInput::make('email')
                            ->email(),
                            Forms\Components\Section::make('')
                            ->visible(fn (Forms\Get $get) => $get("contact_type") == "Individual")
                            ->description('')
                            ->schema([
                                Forms\Components\Select::make('title')
                                ->options([
                                    'Mr' => 'Mr',
                                    'Mrs' => 'Mrs',
                                    'Ms' => 'Ms',
                                    ])->required(),
                                    Forms\Components\TextInput::make('firstname')
                                    ->maxLength(255)
                                    ->required(),
                                    Forms\Components\TextInput::make('surname')
                                    ->maxLength(255)
                                    ->required(),
                                    Forms\Components\TextInput::make('other_names')
                                    ->maxLength(255),
                                    ])
                                    ->columns(2),
                                    
                                    Forms\Components\TextInput::make('additional_contact')
                                    ->maxLength(255),
                                    Forms\Components\TextInput::make('landline')
                                    ->tel()
                                    ->maxLength(255),
                                    ])
                                    ->columns(3),
                                ]),
                                Forms\Components\TextInput::make('reference_no')
                                ->maxLength(255),
                                Forms\Components\DateTimePicker::make('purchase_date')
                                ->required(),
                                Forms\Components\Select::make('purchase_status')
                                ->required()
                                ->options(PurchaseStatus::class)
                                ->searchable(),
                                
                                Forms\Components\Select::make('warehouse_id')
                                ->label('Business Location')
                                ->options(Warehouse::where('is_active',true)->pluck('name','id'))
                                ->helperText('Business location where product will be available for sale')
                                ->searchable()
                                ->live()
                                ->required(),

                                Forms\Components\TextInput::make('per_term')
                                ->label('Payment term')
                                ->maxLength(255),

                                Forms\Components\select::make('per_month')
                                ->label("")
                                ->options([
                                    'Months' => 'Months',
                                    'Days' => 'Days'
                                ])
                                ->searchable(),

                                Forms\Components\FileUpload::make('attach_document'),
                                

                                Forms\Components\Select::make('scan_code')
                                ->label('')
                                ->allowHtml()
                                // ->options(Product::all()->pluck('product_name', 'id'))
                                ->getSearchResultsUsing(fn (string $search, $get): array => (new Saleservice())->getadjustmentproduct($search,$get('warehouse_id')))
                                ->preload()
                                ->placeholder("Scan / Search product by name / code")
                                ->columnSpanFull()
                                ->searchable()
                                ->dehydrated(false)
                                ->hidden( fn ($get) => blank($get("warehouse_id")))
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
                                            $name = $repeaterItems[$key]['product_name'];
                                            $tax_rate = (int) $repeaterItems[$key]['tax_rate'];
                                            
                                            $repeaterItems[$key]['qty'] += 1;
                                            $total = $repeaterItems[$key]['qty'] * $item['unit_price'];
                                            $repeaterItems[$key]['total'] = ($total + (($tax_rate / 100) * $total));
                                            $repeaterItems[$key]['tax'] =  ($tax_rate / 100 * ($repeaterItems[$key]['qty'] * $item['unit_price']));
                                            $stateExists = true;
                                            break; // Exit the loop since the state has been found
                                            
                                        }elseif ($product_type == "Variation" && $item['product_id'] === $state[0] && $item['variant_id'] === $state[1] && $item['warehouse_id'] == $get('warehouse_id')) {
                                            
                                            $currentqty = (int) $repeaterItems[$key]['qty'] + 1;
                                            $stock = (int) $repeaterItems[$key]['stock'];
                                            $name = $repeaterItems[$key]['product_name'];
                                            $tax_rate = (int) $repeaterItems[$key]['tax_rate'];
                                            
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
                                        
                                        $px = $product_variant_data->cost_price;
                                        $selpx = $product_variant_data->selling_price;
                                        
                                    }elseif ($product_type == "Variation") {
                                        
                                        $product_variant_data = $product->inventory()
                                        ->where('variant_id',$state[1])
                                        ->where('warehouse_id',$get('warehouse_id'))
                                        ->first();
                                        
                                        $stock = $product_variant_data->qty;
                                        
                                        $px = $product_variant_data->cost_price;
                                        
                                        $itemcode = $product_variant_data->variant?->item_code ?? null;

                                        $selpx = $product_variant_data->selling_price;
                                        
                                    }
                                    
                                    
                                    $qty = 1;
                                    
                                    $total = $px * $qty;
                                    
                                    $taxrate = Taxrates::whereIn("id",array_values($product->taxes()->pluck('tax_id')->toArray()))->sum('rate');
                                    
                                    //check task method if its exclusive
                                    if ($product->tax_method == "1") {
                                        $tax = (($taxrate / 100) * $total);
                                    }else{
                                        $tax = 0;
                                    }
                                                                                                    
                                    $data = [
                                        'product_name' =>  $product_type == "Variation" ? ($product->product_name."(".$itemcode.")") : ($product->product_name),
                                        'product_id' => $state[0],
                                        'variant_id' => $state[1] ?? null,
                                        'unit_price' => $px,
                                        'selling_price' => $selpx,
                                        'qty' => 1,
                                        'total' => $total + $tax,
                                        'sale_unit_id' => $product->sale_unit_id,
                                        'discount' => 0,
                                        'tax_rate' => $taxrate ?? 0,
                                        'tax' => $tax,
                                        'stock' => $stock,
                                        'warehouse_id' => $get('warehouse_id')
                                    ];
                                    
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
                                    function(Action $action) {
                                        $action->after(fn(Forms\Get $get, Forms\Set $set) => self::updateTotals($get, $set));
                                    }
                                    )
                                    ->reorderable(false)
                                    ->relationship('purchaseitmes')
                                    ->schema([
                                        Forms\Components\Hidden::make('product_id'),
                                        Forms\Components\Hidden::make('variant_id'),
                                        Forms\Components\Hidden::make('sale_unit_id'),
                                        Forms\Components\Hidden::make('stock')
                                        ->dehydrated(false),
                                        
                                        Forms\Components\TextInput::make('product_name')
                                        ->columnSpan(2),

                                        Forms\Components\TextInput::make('qty')
                                        ->integer()
                                        ->default(1)
                                        ->live(onBlur: true)
                                        ->afterStateUpdated(function (Forms\Get $get,Forms\Set $set, $state,$livewire){
                                            $tot = $state * $get('unit_price');
                                                                                        
                                            $set("tax",(($get("tax_rate") / 100) * $tot));

                                            $tax = (($get("tax_rate") / 100) * $tot);
                                            $set("total",($tot + $tax) - $get("discount"));                           
                                            
                                        })
                                        ->afterStateHydrated(function (Forms\Get $get,Forms\Set $set, $state){
                                            $tot = $state * $get('unit_price');
                                                                                        
                                            $set("tax",(($get("tax_rate") / 100) * $tot));

                                            $tax = (($get("tax_rate") / 100) * $tot);

                                            $discount = (int) $get("discount");

                                            $subtotal = ((int) $tot + (int) $tax) - $discount;
                                            $set("total", (int) $subtotal);
                                            
                                        })
                                        ->required(),
                                        
                                        Forms\Components\TextInput::make('unit_price')
                                        ->type("number")
                                        ->label('Unit cost')
                                        ->prefix("GHC")
                                        ->required(),
                                        
                                        Forms\Components\TextInput::make('discount')
                                        ->prefix("GHC")
                                        ->live(onBlur: true)
                                        ->afterStateUpdated(function (Forms\Get $get,Forms\Set $set, $state,$livewire){
                                            $tot = $get("qty") * $get('unit_price');
                                                                                        
                                            $set("tax",(($get("tax_rate") / 100) * $tot));

                                            $tax = (($get("tax_rate") / 100) * $tot);
                                            $set("total",($tot + $tax) - $state);                           
                                            
                                        }),

                                        Forms\Components\TextInput::make('tax_rate')
                                        ->suffix("%"),

                                        Forms\Components\TextInput::make('tax')
                                        ->prefix("GHC"),

                                        // Forms\Components\TextInput::make('selling_price')
                                        // ->type("number")
                                        // ->required(),
                                        
                                        Forms\Components\Placeholder::make('ptotal')
                                        ->content(function ($get,$set){
                                            $tot = ($get("qty") * $get('unit_price'));
                                            
                                           // $set("tax",(($get("tax_rate") / 100) * $tot));
                                            //$set("total",$tot);
                                            
                                            //$set("total",$tot);
                                            return "GHC ".number_format($get("total"),2);
                                        })
                                        ->label('Sub total'),
                                        
                                        Forms\Components\Hidden::make('total')
                                        ->default(0),

                                        Forms\Components\Hidden::make('warehouse_id')

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
                                    
                                    
                                    Forms\Components\Placeholder::make('gtottax')
                                    ->content(function ($get,$set){
                                        
                                        return "Total discount: ".number_format($get("total_dscnt"),2);
                                    })
                                    ->label(''),
                                    
                                    Forms\Components\Placeholder::make('items')
                                    ->content(function ($get,$set){
                                        
                                        return "Net Total Amount: ".number_format((int) $get("total_cost"),2);
                                    })
                                    ->label(''),

                                    Forms\Components\Hidden::make('item')
                                    ->default(0),
                                    
                                    Forms\Components\Hidden::make('total_qty')
                                    ->default(0),

                                    Forms\Components\Hidden::make('total_dscnt')
                                    ->default(0),
                                    
                                    Forms\Components\Select::make('purchasetax')
                                        ->label('Purchase Tax')
                                        ->relationship()
                                        ->multiple()
                                        ->options(Taxrates::pluck('name','id'))
                                        ->searchable()
                                        ->preload()
                                        ->live()
                                        ->afterStateUpdated(function ($state,$get,$set){
                                            self::updateTotals($get,$set);
                                        }),

                                    Forms\Components\Hidden::make('taxpercentage')
                                    ->dehydrated(false),
                                    Forms\Components\Hidden::make('taxpercentage_value')
                                    ->dehydrated(false),
                                    Forms\Components\Hidden::make('discount_value')
                                    ->dehydrated(false),
                                    
                                    
                                    Forms\Components\Select::make('discount_type')
                                    ->label('Discount Type')
                                    ->options([
                                        'Flat' => 'Flat',
                                        'Discount' => 'Discount'
                                        ])
                                        ->searchable()
                                        ->live()
                                        ->afterStateUpdated(function ($get,$set){
                                            self::updateTotals($get,$set);
                                        })
                                        ->required(fn ($get) => !blank($get("discount_amount")))
                                        ->default("Flat"),
                                        
                                        Forms\Components\TextInput::make('discount_amount')
                                        ->required(fn ($get) : bool =>  !blank($get("discount_type")))
                                        ->numeric()
                                        ->default(0.00)
                                        ->live(onBlur: true)
                                        ->afterStateUpdated(function ($get,$set){
                                            logger('dscnt');
                                            self::updateTotals($get,$set);
                                        }),

                                        Forms\Components\TextInput::make('shipping_details')
                                            ->maxLength(255),
                                            
                                        Forms\Components\TextInput::make('shipping_cost')
                                        ->debounce(1000)
                                        ->required()
                                        ->numeric()
                                        ->default(0.00)
                                        ->live(onBlur: true)
                                        ->afterStateUpdated(function ($get,$set){
                                            self::updateTotals($get,$set);
                                        }),
                                        

                                            Forms\Components\Section::make('Other Expenses')
                                            ->description('')
                                            ->schema([
                                                TableRepeater::make('puchaseexpenses')
                                                ->label('')
                                                ->relationship()
                                                ->live()
                                                ->afterStateUpdated(function($state,Forms\Get $get, Forms\Set $set){
                                                    
                                                   logger('purchase exp');
                                                    self::updateTotals($get,$set);
                                                    
                                                })
                                                ->schema([
                                                    
                                                    Forms\Components\TextInput::make('expense_name')
                                                    ->label('Name'),
                                                    
                                                    Forms\Components\TextInput::make('amount')
                                                    ->numeric()
                                                    ->debounce(1000)
                                                    ->default(0),
                                                    
                                                    ])
                                                    ->defaultItems(0)
                                                    ->columnSpanFull()
                                                    ->addActionLabel('Add More Expense'),
                                                    ])
                                                    ->columns(2),

                                                     Forms\Components\Placeholder::make('totalitems')
                                                        ->content(function ($get,$set){
                                                            
                                                            return "Purchase tax: ".$get("taxpercentage")."% (".$get("taxpercentage_value").")";
                                                        })
                                                        ->label(''),
                                                        
                                                        Forms\Components\Placeholder::make('totqtyss')
                                                        ->content(function ($get,$set){
                                                            
                                                            return "Total discount: ".$get("discount_value");
                                                        })
                                                        ->label(''),
                                                        
                                                        
                                                        Forms\Components\Placeholder::make('gtottaxs')
                                                        ->content(function ($get,$set){
                                                            
                                                            return "Total shipping: ".number_format($get("shipping_cost"),2);
                                                        })
                                                        ->label(''),
                                                        
                                                        Forms\Components\Placeholder::make('items')
                                                        ->content(function ($get,$set){
                                                            
                                                            return "Net Grand total: ".number_format((int) $get("grand_total"),2);
                                                        })
                                                        ->label(''),

                                                    Forms\Components\Repeater::make('Payments')
                                                    ->relationship('payments')
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
                                                            
                                                            ])->columns(4),


                                                            ])
                                                            ->defaultItems(1)
                                                            ->columnSpanFull(),

                                                    Forms\Components\Placeholder::make('totalitems')
                                                        ->content(function ($get,$set){
                                                            
                                                            return "Purchase tax: ".$get("taxpercentage")."% (".$get("taxpercentage_value").")";
                                                        })
                                                        ->label(''),
                                                        
                                                        Forms\Components\Placeholder::make('totqtyss')
                                                        ->content(function ($get,$set){
                                                            
                                                            return "Total discount: ".$get("discount_value");
                                                        })
                                                        ->label(''),
                                                        
                                                        
                                                        Forms\Components\Placeholder::make('gtottaxs')
                                                        ->content(function ($get,$set){
                                                            
                                                            return "Total shipping: ".number_format($get("shipping_cost"),2);
                                                        })
                                                        ->label(''),
                                                        
                                                        Forms\Components\Placeholder::make('items')
                                                        ->content(function ($get,$set){
                                                            
                                                            return "Net Grand total: ".number_format((int) $get("grand_total"),2);
                                                        })
                                                        ->label(''),

                                                    Forms\Components\Textarea::make('additional_note')
                                                    ->maxLength(65535)
                                                    ->columnSpanFull(),
                                                    

                                                    Forms\Components\Hidden::make('total_cost')
                                                    ->default(0),
                                                    
                                                    
                                                    Forms\Components\Hidden::make('grand_total')
                                                    ->default(0),
                                                    
                                                    
                                                    
                                                    ])
                                                    ->columns(4),
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

                                                $dscnt = $selectedProducts->pluck('discount')->sum();
                                                $set("total_dscnt",$dscnt);

                                                
                                                $prices = [];
                                                
                                                foreach ($selectedProducts as $selectedProduct) {
                                                    
                                                    $tot = $selectedProduct["qty"] * $selectedProduct['unit_price'];
                                                    $tax =  ($selectedProduct["tax_rate"] / 100) * $tot;
                                                    $dicount = $selectedProduct["discount"];
                                                    $subtotal = ($tot + $tax) - $dicount;
                                                                                                    
                                                    $prices[$selectedProduct['product_id']] = $subtotal;
                                                    
                                                }
                                                
                                                
                                                // Calculate subtotal based on the selected products and quantities
                                                $subtotal = $selectedProducts->reduce(function ($subtotal, $product) use ($prices) {
                                                    return $subtotal + $prices[$product['product_id']];
                                                }, 0);

                                               // logger($get("purchasetax"));

                                              // $tax = Taxrates::where('id',$state)->first();
                                              

                                              logger($get("purchasetax"));
                                              //logger(array_values($get("purchasetax") ?? [])->sum());
                                              

                                               $ratetax = Taxrates::whereIn("id",array_values($get("purchasetax") ?? []))->sum('rate');

                                               $set("taxpercentage",array_sum($get("purchasetax") ?? []));

                                               //logger($ratetax);

                                              //return;
                                                                                                                                                
                                                //add tax
                                                $tax = ((int) $ratetax / 100 ) * $subtotal;

                                                $set("taxpercentage_value",$tax);
                                                                                                
                                                //add shipping cost
                                                $shipping = $get("shipping_cost");
                                                
                                                //substract discount
                                                $order_discount_type = $get("discount_type");
                                                if($order_discount_type == "Flat"){
                                                    $discount = $get("discount_amount");
                                                }elseif($order_discount_type == "Discount"){
                                                    $discount = ($get("discount_amount") / 100) * $subtotal;
                                                }else{
                                                    $discount = $get("discount_amount");
                                                }

                                                $set("discount_value",$discount);
                                                                                                
                                                $grandtotal = ($subtotal + $tax + $shipping) -  ($discount);
                                                
                                                // Update the state with the new values
                                                $set('total_cost', number_format($subtotal, 2, '.', ''));
                                                $set('grand_total', number_format($grandtotal, 2, '.', ''));
                                                
                                                // $set('payment.grand_total', number_format($grandtotal, 2, '.', ''));
                                                
                                                static::calculateitems($set,$get);
                                            } 

                                             public static function calculateitems($set,$get)
                                             {

                                                $state = $get('items');
                                                                                    
                                                $totitem = count(collect($state));
                                                $set("item",$totitem);
                                                
                                                $totalqty = collect($state)
                                                ->pluck('qty')
                                                ->sum();

                                                logger("qty".$totalqty);
                                                
                                                $tottax = collect($state)
                                                ->pluck('tax')
                                                ->sum();
                                                
                                                $set("tottax",$tottax);
                                                
                                                $set("total_qty",$totalqty);
                                                    
                                            }
                                            
                                            public static function table(Table $table): Table
                                            {
                                                return $table
                                                ->recordUrl("")
                                                ->columns([
                                                    Tables\Columns\TextColumn::make('purchase_date')
                                                    ->dateTime()
                                                    ->sortable(),
                                                    
                                                    Tables\Columns\TextColumn::make('reference_no')
                                                    ->searchable(),

                                                    Tables\Columns\TextColumn::make('suppier_id')
                                                    ->label('Supplier')
                                                    ->state(fn (Purchase $record) => $record->suplier?->fullname)
                                                    ->searchable(),
                                                    
                                                    
                                                    Tables\Columns\TextColumn::make('warehouse.name')
                                                    ->label('Location')
                                                    ->numeric()
                                                    ->searchable(),

                                                    Tables\Columns\TextColumn::make('purchase_status')
                                                    ->badge()
                                                    ->sortable(),

                                                    Tables\Columns\TextColumn::make('payment_status')
                                                    ->badge()
                                                    ->sortable(),

                                                    Tables\Columns\TextColumn::make('grand_total')
                                                    ->badge()
                                                    ->sortable()
                                                    ->state(fn ($record) => "GHC".number_format($record->grand_total,2)),

                                                    Tables\Columns\TextColumn::make('balance_amount')
                                                    ->label('Payment due')
                                                    ->badge()
                                                    ->sortable()
                                                    ->state(fn ($record) => "GHC".number_format($record->balance_amount,2))
                                                    ->summarize(Sum::make()->money('GHC')->label('Total due payments')),

                                                    // Tables\Columns\TextColumn::make('per_term')
                                                    // ->label('Payment term')
                                                    // ->state(fn ($record) => $record->per_term." ".$record->per_month)
                                                    // ->searchable()
                                                    // ,

                                                    // Tables\Columns\TextColumn::make('attach_document')
                                                    // ->searchable(),

                                                    // Tables\Columns\TextColumn::make('discount_type')
                                                    // ->searchable(),

                                                    // Tables\Columns\TextColumn::make('discount_amount')
                                                    // ->numeric()
                                                    // ->sortable(),

                                                    // Tables\Columns\TextColumn::make('shipping_details')
                                                    // ->searchable(),

                                                    // Tables\Columns\TextColumn::make('shipping_cost')
                                                    // ->numeric()
                                                    // ->sortable(),

                                                    Tables\Columns\TextColumn::make('recorded_by')
                                                    ->state(fn ($record) => $record->user?->name ?? "")
                                                    ->searchable(),

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

                                                            Tables\Actions\Action::make('view')
                                                            ->label('View')
                                                            ->icon('heroicon-m-eye')
                                                            ->modalHeading(fn ($record) => ucwords(strtolower($record->reference_no))." Purchase Information")
                                                            ->modalWidth(MaxWidth::SixExtraLarge)
                                                            ->modalSubmitAction(false)
                                                            ->modalContent(fn (Purchase $record): View => view(
                                                                'filament.resources.purchase-resource.pages.view-purchase',
                                                                ['record' => $record],
                                                            )),

                                                            Tables\Actions\Action::make("Purchase status")
                                                            ->icon('heroicon-m-check')
                                                            ->color('info')
                                                            ->form([
                                                                
                                                                Forms\Components\Select::make('purchase_status')
                                                                ->required()
                                                                ->options(PurchaseStatus::class)
                                                                ->searchable()
                                                                ])
                                                                ->action(function (array $data,$record): void {   
                                                                    $record->purchase_status = $data['purchase_status'];                                                                                
                                                                    $record->save();
                                                                                                                                        
                                                                    if ($record->purchase_status == PurchaseStatus::Received) {                                                                        (new Saleservice())->deletepreviousitems($record->id);
                                                                        (new Saleservice())->updatedstockitems($record->id);
                                                                    }else {
                                                                        (new Saleservice())->deletepreviousitems($record->id);
                                                                    }
                                                                    
                                                                    //send notification message
                                                                    Notification::make()
                                                                    ->title('Status updated successfully')
                                                                    ->success()
                                                                    ->send();
                                                                }),
                                                                Tables\Actions\EditAction::make(),
                                                                Tables\Actions\DeleteAction::make()
                                                                ->before(function ($record) {
                                                                    $record->items()->delete();
                                                                    $record->purchasetax()->delete();
                                                                    $record->puchaseexpenses()->delete();
                                                                }),

                                                                // Tables\Actions\Action::make('payments')
                                                                // ->label('View Payment')
                                                                // ->icon('heroicon-m-banknotes')
                                                                // ->modalWidth(MaxWidth::SixExtraLarge)
                                                                // ->modalSubmitAction(false)
                                                                // ->modalCancelAction(false)
                                                                // ->modalContent(fn (Purchase $record): View => view(
                                                                //     'filament.resources.purchase-resource.pages.purchase-payment',
                                                                //     ['record' => $record],
                                                                // )),

                                                                Tables\Actions\Action::make('payments')
                                                                ->label('View Payments')
                                                                ->icon('heroicon-m-banknotes')
                                                                ->modalWidth(MaxWidth::SixExtraLarge)
                                                                ->modalHeading("")
                                                                ->modalSubmitAction(false)
                                                                ->modalContent(fn (Purchase $record): View => view(
                                                                    'filament.resources.sales-resource.pages.view-sale-payment',
                                                                    ['record' => $record, 'recordtype' => 'Purchase'],
                                                                )),

                                                                Tables\Actions\Action::make('updatepayments')
                                                                ->label('Update Payment')
                                                                ->icon('heroicon-m-banknotes')
                                                                ->modalWidth(MaxWidth::SixExtraLarge)
                                                                ->modalSubmitAction(false)
                                                                ->modalCancelAction(false)
                                                                ->modalContent(fn (Purchase $record): View => view(
                                                                    'filament.resources.sales-resource.pages.sale-update-payment',
                                                                    ['record' => $record, 'recordtype' => 'Purchase'],
                                                                )),

                                                                ])


                                                            ], position: ActionsPosition::BeforeCells)
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
                                                                    'index' => Pages\ListPurchases::route('/'),
                                                                    'create' => Pages\CreatePurchase::route('/create'),
                                                                    'edit' => Pages\EditPurchase::route('/{record}/edit'),
                                                                ];
                                                            }
                                                        }
                                                        