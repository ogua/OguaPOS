<?php

namespace App\Services;

use App\Filament\CustomForm\Autocomplete;
use App\Models\Clients;
use App\Models\Coupon;
use App\Models\Giftcard;
use App\Models\PaymentAccount;
use App\Models\Product;
use App\Models\Productunit;
use App\Models\Taxrates;
use App\Models\Warehouse;
use Filament\Actions\ActionGroup;
use Filament\Forms;
use Filament\Forms\Components\Actions;
use Filament\Forms\Components\Actions\Action;
use Filament\Notifications\Notification;
use Livewire\Component;
use Icetalker\FilamentTableRepeater\Forms\Components\TableRepeater;

final class SalesForm extends Component{
    
    public static function schema($type) : array {
        return [
            Forms\Components\TextInput::make('received_amount')
            ->live(debounce: 800)
            ->afterStateUpdated(function($state,Forms\Get $get, Forms\Set $set){
                $set("change", $get("paying_amount") - $state);
            })
            ->default(0)
            ->required(),
            
            Forms\Components\TextInput::make('paying_amount')
            ->readonly()
            ->afterStateUpdated(function($state,Forms\Get $get, Forms\Set $set){
                $set("change", $get("received_amount") - $state);
            })
            ->default(0)
            ->required(),
            
            Forms\Components\TextInput::make('change')
            ->default(0)
            ->required(),
            
            Forms\Components\Select::make('paid_by')
            ->options([
                'CASH' => 'CASH',
                'PAYPAL' => 'PAYPAL',
                'CHEQUE' => 'CHEQUE',
                'GIFT CARD' => 'GIFT CARD',
                'CREDIT CARD' => 'CREDIT CARD',
                'DRAFT' => 'DRAFT',
                'BANK TRANSFER' => 'BANK TRANSFER'
                ])
                ->required(),
                
                Forms\Components\TextInput::make('bankname')
                ->label("Bank name")
                ->visible(fn (): bool => $type == "bank")
                ->required(),
                
                Forms\Components\TextInput::make('accountnumber')
                ->label("Account number")
                ->visible(fn (): bool => $type == "bank")
                ->required(),
                
                Forms\Components\TextInput::make('cheque_no')
                ->label("Cheque number")
                ->visible(fn (): bool => $type == "cheque")
                ->required()
                ->columnSpanFull(),
                
                Forms\Components\Hidden::make('gift_card_id')
                ->visible(fn (): bool => $type == "giftcard")
                ->required()
                ->default(""),
                
                Forms\Components\TextInput::make('gift_card')
                ->label("Enter Code")
                ->visible(fn (): bool => $type == "giftcard")
                ->helperText("You can only proceed after the gift card has been successfully added")
                ->default(0)
                ->suffixAction(
                    Action::make("check")
                    ->icon('heroicon-m-clipboard')
                    ->label("Check Card")
                    ->action(function($state, Forms\Set $set, Forms\Get $get){
                        
                        $card = $state;
                        $paying = $get("received_amount");
                        
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
                
                Forms\Components\Select::make('account_id')
                ->label('Account')
                ->options(PaymentAccount::pluck('account_name','id'))
                ->preload()
                ->searchable()
                ->default(""),
                
                Forms\Components\Textarea::make('payment_note')
                ->columnSpanFull(),
                
                Forms\Components\Textarea::make('sale_note'),
                
                Forms\Components\Textarea::make('staff_note'),
            ];
        }
        
        public static function creatformschema() : array {
            return [
                Forms\Components\Section::make('')
                ->description('')
                ->schema([
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


                    // Autocomplete::make('Scan_code')
                    // ->label('Scan Code')
                    // ->allowHtml()
                    // ->searchable()
                    // ->preload()
                    // ->columnSpanFull(),
                    
                    Forms\Components\Select::make('scan_code')
                    ->label('')
                    // ->options(Product::all()->pluck('product_name', 'id'))
                    ->getSearchResultsUsing(fn (string $search, $get): array => (new Saleservice())->getadjustmentproduct($search,$get('warehouse_id')))
                    ->preload()
                    ->allowHtml()
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
                            $itemcode = $product_variant_data->variant?->item_name ?? null;
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
                            'defaultprice' => $px,
                            'default_unit_id' => $product->product_unit_id,
                            'qty' => 1,
                            'total' => $total,
                            'sale_unit_id' => $product->product_unit_id,
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
                        
                        //self::calculateitemsstate($state,$set,$get);
                        self::updateTotals($get,$set);
                        
                    })
                    ->deleteAction(
                        function(Action $action) {
                            $action->after(fn(Forms\Get $get, Forms\Set $set) => self::updateTotals($get, $set));
                        }
                        )
                        ->reorderable(false)
                        ->relationship('saleitem')
                        ->schema([
                            Forms\Components\Hidden::make('product_id'),
                            Forms\Components\Hidden::make('variant_id'),
                            Forms\Components\Hidden::make('stock')
                            ->dehydrated(false),
                            Forms\Components\Hidden::make('defaultprice')
                            ->dehydrated(false),
                            Forms\Components\Hidden::make('default_unit_id')
                            ->dehydrated(false),
                            
                            Forms\Components\TextInput::make('product_name')
                            ->columnSpan(2),
                            Forms\Components\Select::make('sale_unit_id')
                            ->options(function($get){
                                return Productunit::where("base_unit", $get("default_unit_id"))
                                ->orWhere('id',$get("default_unit_id"))->pluck('name','id');
                            })
                            ->preload()
                            ->searchable()
                            ->label("")
                            ->live()
                            ->afterStateUpdated(function ($state,$get,$set){
                                $unit = Productunit::where("id", $state)
                                ->first();
                                
                                if ($unit->base_unit) {
                                    $cpx = (int) $unit->operation_value * $get("defaultprice");
                                    $set("unit_price",$cpx);
                                }else{
                                    $set("unit_price",$get("defaultprice"));
                                }
                            }),
                            
                            Forms\Components\TextInput::make('unit_price')
                            ->type("number")
                            ->readOnly()
                            ->label('Price')
                            ->required(),
                            
                            
                            Forms\Components\TextInput::make('qty')
                            ->integer()
                            ->default(1)
                            ->live(debounce: 500)
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
                            
                            
                            
                            Forms\Components\Section::make('')
                            ->schema([
                                
                                Forms\Components\Hidden::make('tottax')
                                ->dehydrated(false),
                                
                                Forms\Components\Hidden::make('coupon_type')
                                ->dehydrated(false),
                                
                                Actions::make([

                                    Action::make("coupon")
                                    ->icon('heroicon-m-pencil-square')
                                    ->label(fn ($get) => "Coupon: GHC ".number_format($get("coupon_discount"),2))
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
                                            
                                        }),
                                        
                                        Action::make("Tax")
                                        ->icon('heroicon-m-pencil-square')
                                        ->label(fn ($get) =>  "Tax: GHC ".number_format($get("order_tax_rate"),2))
                                        ->modalSubmitActionLabel('Add Tax')
                                        ->form([
                                            Forms\Components\Select::make('m_order_tax')
                                            ->label('')
                                            ->options(Taxrates::pluck('name','id'))
                                            ->searchable()
                                            ->required()
                                            
                                            ])
                                            ->action(function(array $data, Forms\Set $set, Forms\Get $get){
                                                //$set("order_tax", $data["discount_type"]);
                                                $tax = Taxrates::find($data["m_order_tax"]);
                                                
                                                $rate = $tax->rate;
                                                $set("order_tax",$tax->id);
                                                $set("order_tax_rate",$rate);
                                                self::updateTotals($get, $set);
                                            }),
                                            
                                            
                                            Action::make("Shippingcost")
                                            ->icon('heroicon-m-pencil-square')
                                            ->label(fn ($get) =>  "Shipping: GHC ".number_format($get("shipping_cost"),2))
                                            ->modalSubmitActionLabel('Add Shipping cost')
                                            ->form([
                                                
                                                Forms\Components\TextInput::make('setshipping')
                                                ->label('Shipping cost')
                                                ->numeric()
                                                ->default(0),
                                                
                                                ])
                                                ->action(function(array $data, Forms\Set $set, Forms\Get $get){
                                                    $set("shipping_cost",$data["setshipping"]);
                                                    self::updateTotals($get, $set);
                                                }),
                                                
                                                Action::make("Discount")
                                                ->icon('heroicon-m-pencil-square')
                                                ->size('sm')
                                                ->label(fn ($get) => "Discount: GHC ".number_format($get("order_discount_value"),2))
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
                                                        
                                                        
                                                        
                                                    ])
                                                    ->columnSpanFull()
                                                    ->fullWidth(),
                                                    
                                                    Forms\Components\Hidden::make('shipping_cost')
                                                    ->default(0),
                                                    
                                                    Forms\Components\Hidden::make('coupon_discount'),
                                                    
                                                    // Forms\Components\Placeholder::make('coupon_discount')
                                                    // ->content(function ($get,$set){
                                                    
                                                    //     return $get("coupon_discount");
                                                    // })
                                                    // ->label(''),
                                                    
                                                    Forms\Components\Hidden::make('order_tax'),
                                                    Forms\Components\Hidden::make('order_tax_value'),
                                                    
                                                    Forms\Components\Hidden::make('order_tax_rate')
                                                    ->default(0),
                                                    
                                                    
                                                    Forms\Components\Hidden::make('order_discount_type')
                                                    ->default(""),
                                                    
                                                    Forms\Components\Hidden::make('order_discount_value')
                                                    ->default(0),
                                                    
                                                    Forms\Components\Hidden::make('total_price'),
                                                    
                                                    
                                                    Forms\Components\Hidden::make('grand_total'),
                                                    
                                                    Forms\Components\Placeholder::make('pos_grand_total')
                                                    ->columnSpanFull()
                                                    ->extraAttributes([
                                                        'class' => 'text-center bg-red-500 text-white text-12xl font-bold dark:bg-gray-500 p-2'
                                                        ])
                                                        ->content(function ($get,$set){
                                                            
                                                            return "Grand Total: GHC ".number_format($get("grand_total"),2);
                                                        })
                                                        ->label(''),
                                                        
                                                        
                                                        ])
                                                        ->columns(4),
                                                        
                                                        
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
                                                        Forms\Components\Hidden::make('cash_register_id'),
                                                        Forms\Components\Hidden::make('biller_id'),
                                                        
                                                        //payments
                                                        // Forms\Components\TextInput::make('payment.user_id')
                                                        // ->default(auth()->user()->id),
                                                        // Forms\Components\TextInput::make('payment.amount')
                                                        // ->default(0),
                                                        // Forms\Components\TextInput::make('payment.change')
                                                        // ->default(0),
                                                        // Forms\Components\TextInput::make('payment.customer_id'),
                                                        // Forms\Components\TextInput::make('payment.paying_method'),
                                                        // Forms\Components\TextInput::make('payment.payment_note')
                                                        
                                                        ])
                                                        ->columns(4),
                                                    ];
                                                }
                                                
                                                
                                                
                                                
                                                
                                                public static function Editformschema() : array {
                                                    return [
                                                        Forms\Components\Section::make('')
                                                        ->description('')
                                                        ->schema([
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
                                                            ->getSearchResultsUsing(fn (string $search, $get): array => (new Saleservice())->getadjustmentproduct($search,$get('warehouse_id')))
                                                            ->preload()
                                                            ->allowHtml()
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
                                                                        $name = $repeaterItems[$key]['product_name'];
                                                                        $tax_rate = (int) $repeaterItems[$key]['tax_rate'];
                                                                        
                                                                        
                                                                        if($currentqty > $stock){
                                                                            
                                                                            Notification::make()
                                                                            ->title($name. ' is out of stock!')
                                                                            ->body("In-stock: ".$stock."(".$product->unit?->code.")")
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
                                                                        $name = $repeaterItems[$key]['product_name'];
                                                                        $tax_rate = (int) $repeaterItems[$key]['tax_rate'];
                                                                        
                                                                        if($currentqty > $stock){
                                                                            
                                                                            Notification::make()
                                                                            ->title($name. ' is out of stock!')
                                                                            ->body("In-stock: ".$stock."(".$product->unit?->code.")")
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
                                                                    
                                                                    $px = $product_variant_data->selling_price;
                                                                    
                                                                    $itemcode = $product_variant_data->variant?->item_name ?? null;
                                                                    
                                                                }
                                                                
                                                                //check promotional
                                                                if ($promo) {
                                                                    $px = $promo->promotion_price ?? 0;
                                                                }
                                                                
                                                                $qty = 1;
                                                                
                                                                $total = $px * $qty;
                                                                
                                                                $tax = Taxrates::whereIn("id",array_values($product->taxes()->pluck('tax_id')->toArray()))->sum('rate');
                                                                
                                                                //check task method if its exclusive
                                                                if ($product->tax_method == "1") {
                                                                    $newpx = ($total + ($tax / 100) * $total);
                                                                }else{
                                                                    $newpx = $px;
                                                                }
                                                                
                                                                $px = $newpx;
                                                                
                                                                if ($stock < 1) {
                                                                    
                                                                    // $this->dispatch('play-sound');
                                                                    
                                                                    Notification::make()
                                                                    ->title($product_type == "Variation" ? ($product->product_name.' '.$itemcode) : ($product->product_name). ' is out of stock!')
                                                                    ->body("In-stock: ".$stock."(".$product->unit?->code.")")
                                                                    ->warning()
                                                                    ->send();
                                                                    
                                                                    $set("scan_code","");
                                                                    
                                                                    return;
                                                                }
                                                                
                                                                $total = $px;
                                                                
                                                                $data = [
                                                                    'product_name' =>  $product_type == "Variation" ? ($product->product_name.' '.$itemcode." in-stock: ".$stock) : ($product->product_name." in-stock: ".$stock),
                                                                    'product_id' => $state[0],
                                                                    'variant_id' => $state[1] ?? null,
                                                                    'unit_price' => $px,
                                                                    'defaultprice' => $px,
                                                                    'default_unit_id' => $product->product_unit_id,
                                                                    'qty' => 1,
                                                                    'total' => $total,
                                                                    'sale_unit_id' => $product->product_unit_id,
                                                                    'discount' => 0,
                                                                    'tax_rate' => $tax ?? 0,
                                                                    'tax' => $product->tax_method == "1" ? (($tax / 100) * $total) : 0,
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
                                                                
                                                                //self::calculateitemsstate($state,$set,$get);
                                                                self::updateTotals($get,$set);
                                                                
                                                            })
                                                            ->deleteAction(
                                                                function(Action $action) {
                                                                    $action->after(fn(Forms\Get $get, Forms\Set $set) => self::updateTotals($get, $set));
                                                                }
                                                                )
                                                                ->reorderable(false)
                                                                ->relationship('saleitem')
                                                                ->schema([
                                                                    Forms\Components\Hidden::make('product_id'),
                                                                    Forms\Components\Hidden::make('variant_id'),
                                                                    Forms\Components\Hidden::make('stock')
                                                                    ->dehydrated(false),
                                                                    Forms\Components\Hidden::make('defaultprice')
                                                                    ->dehydrated(false),
                                                                    Forms\Components\Hidden::make('default_unit_id')
                                                                    ->dehydrated(false),
                                                                    
                                                                    Forms\Components\TextInput::make('product_name')
                                                                    ->columnSpan(2),
                                                                    
                                                                    Forms\Components\Select::make('sale_unit_id')
                                                                    ->options(function($get){
                                                                        return Productunit::where("base_unit", $get("default_unit_id"))
                                                                        ->orWhere('id',$get("default_unit_id"))->pluck('name','id');
                                                                    })
                                                                    ->preload()
                                                                    ->searchable()
                                                                    ->label("")
                                                                    ->live()
                                                                    ->afterStateUpdated(function ($state,$get,$set){
                                                                        $unit = Productunit::where("id", $state)
                                                                        ->first();
                                                                        
                                                                        if ($unit->base_unit) {
                                                                            $cpx = (int) $unit->operation_value * $get("defaultprice");
                                                                            $set("unit_price",$cpx);
                                                                        }else{
                                                                            $set("unit_price",$get("defaultprice"));
                                                                        }
                                                                    }),
                                                                    
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
                                                                        
                                                                        logger("total updated: ".($tot + ($get("tax_rate") / 100) * $tot));
                                                                    })
                                                                    ->afterStateHydrated(function (Forms\Get $get,Forms\Set $set, $state){
                                                                        $tot = $state * $get('unit_price');
                                                                        
                                                                        $set("tax",(($get("tax_rate") / 100) * $tot));
                                                                        $set("total",($tot + ($get("tax_rate") / 100) * $tot));
                                                                        
                                                                        logger("total hydrated: ".($tot + ($get("tax_rate") / 100) * $tot));
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
                                                                    
                                                                    Forms\Components\Hidden::make('tottax')
                                                                    ->dehydrated(false),
                                                                    
                                                                    
                                                                    Forms\Components\Section::make('')
                                                                    ->schema([
                                                                                                                                                
                                                                        

                            Forms\Components\Section::make('')
                            ->schema([
                                
                                Forms\Components\Hidden::make('tottax')
                                ->dehydrated(false),
                                
                                Forms\Components\Hidden::make('coupon_type')
                                ->dehydrated(false),
                                
                                Actions::make([

                                    Action::make("coupon")
                                    ->icon('heroicon-m-pencil-square')
                                    ->label(fn ($get) => "Coupon: GHC ".number_format($get("coupon_discount"),2))
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
                                            
                                        }),
                                        
                                        Action::make("Tax")
                                        ->icon('heroicon-m-pencil-square')
                                        ->label(fn ($get) =>  "Tax: GHC ".number_format($get("order_tax_rate"),2))
                                        ->modalSubmitActionLabel('Add Tax')
                                        ->form([
                                            Forms\Components\Select::make('m_order_tax')
                                            ->label('')
                                            ->options(Taxrates::pluck('name','id'))
                                            ->searchable()
                                            ->required()
                                            
                                            ])
                                            ->action(function(array $data, Forms\Set $set, Forms\Get $get){
                                                //$set("order_tax", $data["discount_type"]);
                                                $tax = Taxrates::find($data["m_order_tax"]);
                                                
                                                $rate = $tax->rate;
                                                $set("order_tax",$tax->id);
                                                $set("order_tax_rate",$rate);
                                                self::updateTotals($get, $set);
                                            }),
                                            
                                            
                                            Action::make("Shippingcost")
                                            ->icon('heroicon-m-pencil-square')
                                            ->label(fn ($get) =>  "Shipping: GHC ".number_format($get("shipping_cost"),2))
                                            ->modalSubmitActionLabel('Add Shipping cost')
                                            ->form([
                                                
                                                Forms\Components\TextInput::make('setshipping')
                                                ->label('Shipping cost')
                                                ->numeric()
                                                ->default(0),
                                                
                                                ])
                                                ->action(function(array $data, Forms\Set $set, Forms\Get $get){
                                                    $set("shipping_cost",$data["setshipping"]);
                                                    self::updateTotals($get, $set);
                                                }),
                                                
                                                Action::make("Discount")
                                                ->icon('heroicon-m-pencil-square')
                                                ->size('sm')
                                                ->label(fn ($get) => "Discount: GHC ".number_format($get("order_discount_value"),2))
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
                                                        
                                                        
                                                        
                                                    ])
                                                    ->columnSpanFull()
                                                    ->fullWidth(),
                                                    
                                                    Forms\Components\Hidden::make('shipping_cost')
                                                    ->default(0),
                                                    
                                                    Forms\Components\Hidden::make('coupon_discount'),
                                                    
                                                    // Forms\Components\Placeholder::make('coupon_discount')
                                                    // ->content(function ($get,$set){
                                                    
                                                    //     return $get("coupon_discount");
                                                    // })
                                                    // ->label(''),
                                                    
                                                    Forms\Components\Hidden::make('order_tax'),
                                                    Forms\Components\Hidden::make('order_tax_value'),
                                                    
                                                    Forms\Components\Hidden::make('order_tax_rate')
                                                    ->default(0),
                                                    
                                                    
                                                    Forms\Components\Hidden::make('order_discount_type')
                                                    ->default(""),
                                                    
                                                    Forms\Components\Hidden::make('order_discount_value')
                                                    ->default(0),
                                                    
                                                    Forms\Components\Hidden::make('total_price'),
                                                    
                                                    
                                                    Forms\Components\Hidden::make('grand_total'),
                                                    
                                                    Forms\Components\Placeholder::make('pos_grand_total')
                                                    ->columnSpanFull()
                                                    ->extraAttributes([
                                                        'class' => 'text-center bg-red-500 text-white text-12xl font-bold dark:bg-gray-500 p-2'
                                                        ])
                                                        ->content(function ($get,$set){
                                                            
                                                            return "Grand Total: GHC ".number_format($get("grand_total"),2);
                                                        })
                                                        ->label(''),
                                                        
                                                        
                                                        ])
                                                        ->columns(4),
                                                                                    
                                                                                    
                                                                                    Forms\Components\Hidden::make('total_price')
                                                                                    ->label('Sub Total')
                                                                                    ->default(0),
                                                                                    
                                                                                    
                                                                                    ])
                                                                                    ->columns(6),
                                                                                    
                                                                                    
                                                                                    Forms\Components\Hidden::make('item')
                                                                                    ->default(0),
                                                                                    Forms\Components\Hidden::make('total_qty')
                                                                                    ->default(0),
                                                                                    Forms\Components\Hidden::make('currency_id'),
                                                                                    
                                                                                    Forms\Components\Hidden::make('grand_total')
                                                                                    ->default(fn ($get) => $get("total_price")),
                                                                                    
                                                                                    Forms\Components\Hidden::make('paid_amount')
                                                                                    ->default(0),
                                                                                    Forms\Components\Hidden::make('total_discount')
                                                                                    ->default(0),
                                                                                    
                                                                                    Forms\Components\Hidden::make('cash_register_id'),
                                                                                    Forms\Components\Hidden::make('biller_id'),
                                                                                                                                                                    
                                                                                    Forms\Components\Repeater::make('Payments')
                                                                                    ->label("")
                                                                                    ->relationship('payments')
                                                                                    ->schema([
                                                                                        
                                                                                        Forms\Components\Section::make('Payments')
                                                                                        ->description('')
                                                                                        ->schema([
                                                                                            
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
                                                                                                
                                                                                                Forms\Components\TextInput::make('amount')
                                                                                                ->label('Amount paid')
                                                                                                ->default(0),
                                                                                                
                                                                                                
                                                                                                Forms\Components\TextInput::make('change')
                                                                                                ->default(0),
                                                                                                
                                                                                                Forms\Components\Select::make('account_id')
                                                                                                ->label('Account')
                                                                                                ->options(PaymentAccount::pluck('account_name','id'))
                                                                                                ->preload()
                                                                                                ->searchable(),
                                                                                                
                                                                                                Forms\Components\Hidden::make('customer_id'),
                                                                                                
                                                                                                Forms\Components\Textarea::make('payment_note')
                                                                                                ->columnSpanFull(),
                                                                                                
                                                                                                ])->columns(4),
                                                                                                
                                                                                                
                                                                                                ])->columnSpanFull(),
                                                                                                
                                                                                                
                                                                                                Forms\Components\Section::make('')
                                                                                                ->description('')
                                                                                                ->schema([
                                                                                                    
                                                                                                    
                                                                                                    Forms\Components\Textarea::make('sale_note')
                                                                                                    ->columnSpan(2),
                                                                                                    
                                                                                                    Forms\Components\Textarea::make('staff_note')
                                                                                                    ->columnSpan(2),
                                                                                                    
                                                                                                    
                                                                                                    
                                                                                                    ])
                                                                                                    ->columns(4),
                                                                                                    
                                                                                                    
                                                                                                    
                                                                                                    
                                                                                                    ])
                                                                                                    ->columns(4),
                                                                                                ];
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
                                                                                                
                                                                                                $set("tottax",$tottax);
                                                                                                
                                                                                                $set("total_qty",$totalqty);
                                                                                                
                                                                                            }
                                                                                            
                                                                                            public static function calculateitemsstate($state, $set,$get) {
                                                                                                
                                                                                                $totitem = count(collect($state));
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
                                                                                            
                                                                                            
                                                                                            
                                                                                            
                                                                                            
                                                                                            
                                                                                            
                                                                                            
                                                                                            
                                                                                        }