<?php

namespace App\Livewire\Pos;

use App\Models\Brand;
use App\Models\Cashregister;
use App\Models\Clients;
use App\Models\Coupon;
use App\Models\Giftcard;
use App\Models\Payment;
use App\Models\PaymentAccount;
use App\Models\Possettings;
use App\Models\Product;
use App\Models\Product_Warehouse_Inventory;
use App\Models\Productcategory;
use App\Models\ProductPromotion;
use App\Models\Sales;
use App\Models\Stock_History;
use App\Models\Taxrates;
use App\Models\Warehouse;
use App\Partials\Enums\PaymentStatus;
use App\Partials\Enums\PurchaseStatus;
use App\Partials\Enums\SalesStatus;
use App\Services\Saleservice;
use App\Support\ProductSearchImage;
use Carbon\Carbon;
use Livewire\Component;
use Filament\Forms;
use Filament\Forms\Components\Actions\Action;
use Filament\Forms\Components\Group;
use Filament\Forms\Concerns\InteractsWithForms;
use Filament\Forms\Contracts\HasForms;
use Filament\Forms\Form;
use Filament\Notifications\Notification;
use Filament\Notifications\Actions\Action as NotificationAction;
use Icetalker\FilamentTableRepeater\Forms\Components\TableRepeater;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Redirect;
use Livewire\Attributes\Validate;


class EditPosComponent extends Component implements HasForms
{
    use InteractsWithForms;

    public $record;
    
    public ?array $data = [];
    
    public $received = 0;
    public $paying = 0;
    public $change = 0;

   // #[Validate('required', message: 'Please choose a payment method')]
    public $paidby = "";

    public $payment_note = "";
    public $sale_note = "";
    public $staffnote = "";

   // #[Validate('required', message: 'Please provide a cheque number')]
    public $cheque_no = "";

    public $gift_card_id = "";
    public $gift_card = "";

    public ?array $allproducts = [];
    public $defaultProducts;
    public $brand;
    public $category;
    public $filtercategory = "";
    public $filterbrand = "";

    public $payby = ""; 
    public $bankname = ""; 
    public $accountnumber = "";


    public $cash_register;
    public $cash_register_id;

    public $pos;

    public $variationitems = [];
    public $giftcards;

    public $salesSummary;
    public $salesbrandSummary;

    public $payingaccount;
    public $account_id = "";
    
    public function mount() {

        $sale = Sales::where('id',$this->record)->first();
        
        $this->form->fill($sale->toArray());

        
        $warehouse_id = $sale->warehouse_id;

        $cashreg = Cashregister::where('id',$sale->cash_register_id)->first();

        $pos = Possettings::where('warehouse_id',$warehouse_id)->first();

        $this->payingaccount = PaymentAccount::latest()->get();

        $this->pos = $pos;

        if (!$cashreg) {
            $role = auth()->user()->role;
            if ($role == "admin") {
                return redirect()->to('/admin/cash-register/create');
            }

            return redirect()->to('/cashier/cash-register/create');
           
        }

        $this->cash_register = $cashreg;
        $this->cash_register_id = $cashreg->id;
        $this->data["biller_id"] = $pos->biller_id;
        $this->data["cash_register_id"] = $cashreg->id;

        
        $this->brand = Brand::all();
        $this->category = Productcategory::all();
        
        $products = [];
        // Load Single type products
        $singleproduct = Product::whereHas('warehouses',function($query) use($warehouse_id){
            $query->where('warehouse_id', $warehouse_id);
        })
        ->where('active', true)
       // ->where('product_type', 'Single')
        ->get();

        foreach ($singleproduct as $product) {
            
            $main = $product->product_name ?? "";

                $productData = [
                    'id' => $product->id,
                    'product_name' => $main,
                    'product_type' => $product->product_type,
                    'product_code' => $product->item_name ?? "",
                    'product_image' => $product->product_image,
                    'product_expiry_date' =>$product->product_expiry_date,
                    'brandname' => $product->brand?->name ?? null,
                    'brandid' => $product->brand?->id ?? null,
                    'categoryid' => $product->category?->id ?? null,
                    'categoryname' => $product->category?->name ?? null, 
                ];

                $products[] = $productData;
        }
        
        $this->allproducts = $products;
        $this->defaultProducts = $products;


        $repeaterItems = [];

        foreach ($sale->saleitem as $product) {

            if ($product->variant_id) {
                
                $stock = Product_Warehouse_Inventory::where('product_id',$product->product_id)
                ->where('warehouse_id',$product->warehouse_id)
                ->where('variant_id',$product->variant_id)
                ->first();

            }else {
                
                $stock = Product_Warehouse_Inventory::where('product_id',$product->product_id)
                ->where('warehouse_id',$product->warehouse_id)
                ->first();
            }

            
            $data = [
            'product_name' =>  $product->product_name,
            'product_id' => $product->product_id,
            'variant_id' => $product->variant_id ?? null,
            'unit_price' =>  $product->unit_price,
            'display_price' => $product->unit_price + $product->tax,
            'qty' => $product->qty,
            'total' => $product->total,
            'sale_unit_id' => $product->sale_unit_id,
            'discount' => $product->discount,
            'tax_rate' => $product->tax_rate ?? 0,
            'tax' => $product->tax,
            'stock' => $stock->qty ?? 0,
            'warehouse_id' => $product->warehouse_id
        ];
        
        array_push($repeaterItems, $data);

        }

        $this->data['items'] = $repeaterItems;
        self::calculateitemsmanual();
        self::updateTotalsmanual();

    }

    public function filterProducts()
    {
        $filteredProducts = $this->defaultProducts;
        
        if (!empty($this->filtercategory)) {
            // Filter products by brandid
            $filteredProducts = array_filter($filteredProducts, function ($product) {
                return $product['categoryid'] == $this->filtercategory;
            });

        }
        if (!empty($this->filterbrand)) {

            $filteredProducts = array_filter($filteredProducts, function ($product) {
                return $product['brandid'] == $this->filterbrand;
            });
        }

        // If both filters are empty, reset to default products
        if (empty($this->filtercategory) && empty($this->filterbrand)) {
            $this->allproducts = $this->defaultProducts;
        }else {
            $this->allproducts = $filteredProducts;
        }
        
    }


    public function checkgiftcard()  {

        $card = $this->gift_card;
        $paying = $this->received;

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

               $this->gift_card_id = $cards->id;
            }

        }else{

            Notification::make()
                    ->title("Gift Card expired or dont exist!")
                    ->warning()
                    ->send();

            $this->gift_card_id = "";
        }

    }

    
    public function create()
    {
        

         $this->validate([ 
                'received' => 'required',
                'paying' => 'required',
                'change' => 'required',
                'paidby' => 'required',
            ]);
       
       
       
        if ($this->paidby == "CHEQUE") {
            
            $this->validate([ 
                'cheque_no' => 'required',
            ]);

        }elseif ($this->paidby == "GIFT CARD") {

            $this->validate([ 
                'gift_card_id' => 'required',
            ]);

        }elseif ($this->paidby == "BANK TRANSFER") {

            $this->validate([ 
                'bankname' => 'required',
                'accountnumber' => 'required',
            ]);
        }

        if ($this->paidby == "CREDIT CARD") {
            //check if its credit card payment
            $this->dispatch('close-modal', id: 'pay-with-cash');
            $amount = $this->received;
            $paid = $this->paying;
            $change = $this->change;

            $this->dispatch('play-error');

            Notification::make()
            ->title('Paypal Checkout coming soon!')
            ->warning()
            ->send();

            return;
            

        }elseif ($this->paidby == "PAYPAL") {
            # //check if its paypal payment
            $this->dispatch('close-modal', id: 'pay-with-cash');

            $this->dispatch('play-error');

            Notification::make()
            ->title('Paypal Checkout coming soon!')
            ->warning()
            ->send();

            return;
        }

        
        $this->data["sale_status"] = SalesStatus::Pending;
        $this->data["payment_status"] = PurchaseStatus::Pending;
        $this->data["currency_id"] = $this->pos->currency;
        $this->data["paid_amount"] = $this->received;
        $this->data["balance_amount"] = (int) $this->paying - (int) $this->received;
        $this->data["cash_register_id"] = $this->cash_register_id;
        $this->data["sale_note"] = $this->sale_note;
        $this->data["staff_note"] = $this->staffnote;
        $this->data["payment_note"] = $this->payment_note;
        $this->data["reference_number"] = blank($this->data["reference_number"]) ? "posr-".date('Ymd')."-".date('hms') : $this->data["reference_number"];
        
        $sale = new Sales($this->form->getState());
        $sale->save();
        

         $paymentinsert = [
            'user_id' => $this->data["user_id"],
            'purchase_id' => null,
            'sale_id' => $sale->id,
            'cash_register_id' => $this->cash_register_id,
            'account_id' => $this->account_id ?? null,
            'amount' => $this->received,
            'used_points' => null,
            'change' => $this->change,
            'cheque_no' => $this->cheque_no,
            'customer_id' => $this->data["customer_id"],
            'customer_stripe_id' => null,
            'charge_id' => null,
            'gift_card_id' => (int) $this->gift_card_id ?? null,
            'paypal_transaction_id' => null,
            'paying_method' => $this->paidby,
            'payment_note' => $this->payment_note,
            'bankname' => $this->bankname,
            'accountnumber' => $this->accountnumber,
            'payment_type' => "debit",
            'payment_ref' => "SPP-".date('Ymd')."-".date('hms'),
            'paying_type' => "Sale",
            'paid_on' => now(),
        ];

        $paymentdata = new Payment($paymentinsert);
        $paymentdata->save();

        $paycc = PaymentAccount::where('id',$this->account_id)->first();
        if ($paycc) {
            $balance = $paycc->current_balance + $this->received;
            $paycc->current_balance = $balance;
            $paycc->save();
        }
        
        $this->form->model($sale)->saveRelationships();
        
        $this->form->fill();

        //check if giftcard
        if ($this->gift_card_id) {

            $paying = $this->paying;

            $card = Giftcard::where('id',$this->gift_card_id)->first();

            $expense = $card->expense + $paying;

            $card->expense = $expense;
            $card->save();
        }

        //check coupon
        $coupon_id = $sale->coupon_id;
        
        $coupon = Coupon::where('id',$coupon_id)->first();
        
        if ($coupon) {
            $qty = $coupon->qty;
            $used = (int) $coupon->used++;
            $available = $qty  - $used;
            
            $coupon->available = $available;
            $coupon->save();
        }


        
        // if ($this->paidby == "CREDIT CARD") {
        //     //check if its credit card payment
        //     $this->dispatch('close-modal', id: 'pay-with-cash');
        //     $amount = $this->received;
        //     $paid = $this->paying;
        //     $change = $this->change;

        //     $this->dispatch('play-error');

        //     Notification::make()
        //     ->title('Paypal Checkout coming soon!')
        //     ->warning()
        //     ->send();

        //     return;
            

        // }elseif ($this->paidby == "PAYPAL") {
        //     # //check if its paypal payment
        //     $this->dispatch('close-modal', id: 'pay-with-cash');

        //     $this->dispatch('play-error');

        //     Notification::make()
        //     ->title('Paypal Checkout coming soon!')
        //     ->warning()
        //     ->send();

        //     return;
        // }

        $sale->sale_status = SalesStatus::Completed;
        $sale->payment_status = PaymentStatus::Paid;
        $sale->save();
        
        //update quantity
        foreach (collect($sale->saleitem) as $row) {

            $product = Product::where('id', $row->product_id)->first();

            $product_type = $product->product_type;
            $warehouse = $sale->warehouse_id;

            if($product_type == "Single"){

                $stock = Product_Warehouse_Inventory::where('product_id',$product->id)
                ->where('warehouse_id',$warehouse)
                ->first();

                $totalqty = $stock->qty - $row->qty;
                $stock->qty = $totalqty;
                $stock->save();

                //update history
                $avaliableqty = $stock->qty;

                //update stock from warehouse history
                $stockout = [
                    'product_id' => $product->id,
                    'warehouse_id' => $warehouse,
                    'adjustment_item_id' => $sale->id,
                    'type' => 'Sales',
                    'qty_change' => "-".$row->qty,
                    'new_quantity' => $avaliableqty,
                    'date' => now(),
                    'reference' => $sale->reference_number
                ];
                
                Stock_History::create($stockout);

            }elseif ($product_type == "Variation"){

                $stock = Product_Warehouse_Inventory::where('product_id',$product->id)
                ->where('warehouse_id',$warehouse)
                ->where('variant_id',$row->variant_id)
                ->first();

                $totalqty = $stock->qty - $row->qty;
                $stock->qty = $totalqty;
                $stock->save();

                //update history
                $avaliableqty = $stock->qty;

                //update stock from warehouse history
                $stockout = [
                    'product_id' => $product->id,
                    'warehouse_id' => $warehouse,
                    'variant_id' => $row->variant_id,
                    'adjustment_item_id' => $sale->id,
                    'type' => 'Sales',
                    'qty_change' => "-".$row->qty,
                    'new_quantity' => $avaliableqty,
                    'date' => now(),
                    'reference' => $sale->reference_number
                ];
                
                Stock_History::create($stockout); 

            }

        }
        

        Notification::make()
        ->title('Recorded successfully!')
        ->success()
        ->body('Sales has been saved.')
        ->persistent()
        ->actions([
            NotificationAction::make('print')
            ->url(route('pos-invoice', $sale->id), shouldOpenInNewTab: true)
            ->button(),
        ])
        ->send();

        $this->dispatch('close-modal', id: 'pay-with-cash');

        $this->dispatch('printInvoice', $sale->id);
    }

    public function opencashregister() {
        $date = date('Y-m-d', strtotime($this->cash_register->created_at));


        $salesSummary = DB::table('sales')
        ->join('payments', 'sales.id', '=', 'payments.sale_id')
        ->where('sales.cash_register_id',$this->cash_register->id)
        ->select('payments.paying_method', DB::raw('SUM(payments.amount) as total_amount'))
        ->groupBy('payments.paying_method')
        ->get();

        // Transform the results into an associative array
        $salesSummaryArray = $salesSummary->pluck('total_amount', 'paying_method')->toArray();

        $this->salesSummary = $salesSummaryArray;

        
        $salesbrandSummary = DB::table('sales')
            ->join('sales_items', 'sales.id', '=', 'sales_items.sale_id')
            ->join('products', 'sales_items.product_id', '=', 'products.id')
            ->leftJoin('brands', 'products.brand_id', '=', 'brands.id')
            ->where('sales.cash_register_id',$this->cash_register->id)
            ->select(
                DB::raw('COALESCE(brands.name, "-") as brand_name'),
                DB::raw('SUM(sales_items.qty) as total_qty'),
                DB::raw('SUM(sales_items.total) as brand_total'),
            )
            ->groupBy('brands.name')
            ->get();

        // Convert to associative array for easier handling in the view
        $salesSummaryArray = $salesbrandSummary->mapWithKeys(function ($item) {
            return [$item->brand_name => $item->total_qty.','.$item->brand_total];
        })->toArray();
            

        $this->salesbrandSummary = $salesSummaryArray;

        $this->dispatch('open-modal', id: 'cash-register-details');
    }

    public function closecashregister() {
         $this->dispatch('open-modal', id: 'cash-register-details');
    }
    
    public function openpaywithcashmodal($payby)
    {
        $this->paidby = $payby;
        
        $this->dispatch('play-sound');
        $this->dispatch('open-modal', id: 'pay-with-cash');
    }
    
    
    public function addtoitems($key, $product, $type){
        
        $state = explode(",",$product);
        
        $repeaterItems = $this->data['items'];
        
        $warehouse_id = $this->data["warehouse_id"];
        
        $product = Product::find($state[0]);
        
        // Flag to check if state exists
        $stateExists = false;
        
        $product_type = $product->product_type;

        if($type != "pass"){

            if ($product_type == "Variation") {

                $this->variationitems = [];

                return self::loadvariationmodal($product);
            }

        }

        $this->dispatch('success-sound');
        
        // Loop through the items array
        foreach ($repeaterItems as $key => $item) {

            if ($product_type == "Single" && $item['product_id'] === $state[0] && $item['warehouse_id'] == $warehouse_id) {

                $currentqty = (int) $repeaterItems[$key]['qty'] + 1;
                $stock = (int) $repeaterItems[$key]['stock'];
                $name = $repeaterItems[$key]['product_name'];
                $tax_rate = (int) $repeaterItems[$key]['tax_rate'];
                
                if($currentqty > $stock){

                    $this->dispatch('play-error');
                    
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

            }elseif ($product_type == "Variation" && $item['product_id'] === $state[0] && $item['variant_id'] === $state[1] && $item['warehouse_id'] == $warehouse_id) {
                
                $currentqty = (int) $repeaterItems[$key]['qty'] + 1;
                $stock = (int) $repeaterItems[$key]['stock'];
                $name = $repeaterItems[$key]['product_name'];
                $tax_rate = (int) $repeaterItems[$key]['tax_rate'];
                
                if($currentqty > $stock){

                    $this->dispatch('play-error');
                    
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
        ->when($product_type == "Single",function($query) use($warehouse_id){
            return $query->where('warehouse_id',$warehouse_id);
        })
        ->when($product_type == "Variation",function($query) use($state,$warehouse_id){
            return $query->where('warehouse_id',$warehouse_id)
            ->where('variant_id',$state[1]);
        })
        ->when($product_type == "Combo",function($query) use($state,$warehouse_id){
            return $query->where('warehouse_id',$warehouse_id)
            ->where('variant_id',$state[1]);
        })
        ->activepromo()
        ->currentdate()->first();

        if ($product_type == "Single") {

                $product_variant_data = $product->inventory()
                ->where('warehouse_id',$warehouse_id)
                ->first();

                $stock = (int) $product_variant_data->qty;

                $px = $product_variant_data->selling_price;

                
        }elseif ($product_type == "Variation") {

                $product_variant_data = $product->inventory()
                ->where('variant_id',$state[1])->first();

                $stock = (int) $product_variant_data->qty;

                $px = $product_variant_data->selling_price;

                $itemcode = $product_variant_data->variant?->item_name ?? null;
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
            
            $this->dispatch('play-error');
            
            Notification::make()
            ->title($product_type == "Variation" ? ($product->product_name.' '.$itemcode) : ($product->product_name). ' is out of stock!')
            ->body("In-stock: ".$stock."(".$product->unit?->code.")")
            ->warning()
            ->send();
            
            return;
        }
        
        $qty = 1;
        $total = $px * $qty;
        
        $data = [
            //'product_name' =>  $product_type == "Variation" ? ($product->product_name.' '.$itemcode." in-stock: ".$stock) : ($product->product_name." in-stock: ".$stock),
            'product_name' =>  $product_type == "Variation" ? ($product->product_name.' '.$itemcode) : ($product->product_name),
            'product_id' => $state[0],
            'variant_id' => $state[1] ?? null,
            'unit_price' => $px,
            'display_price' => $product->tax_method == "1" ? ($px + (($tax / 100) * $px)) : $px,
            'qty' => 1,
            'total' => $total,
            'sale_unit_id' => $product->sale_unit_id,
            'discount' => 0,
            'tax_rate' => $tax ?? 0,
            'tax' => $product->tax_method == "1" ? (($tax / 100) * $px) : 0,
            'stock' => $stock,
            'warehouse_id' => $warehouse_id
        ];
        
        // If state doesn't exist, add it to the array
        if (!$stateExists) {
            array_push($repeaterItems, $data);
        }

        $this->data['items'] = $repeaterItems;
                        
        self::calculateitemsmanual();
        self::updateTotalsmanual();

        $this->dispatch('close-modal', id: 'variation-items');
    }


    public function loadvariationmodal($product) {

        //$this->dispatch('success-sound');

        $products = [];

         $product_variant_data = $product->variationitems()->orderBy('position')->get();
            
            $main = $product->product_name ?? "";
            
            foreach ($product_variant_data as $row) {
                $productData = [
                    'id' => $product->id.','.$row->id,
                    'product_name' => $main . "($row->item_name)",
                    'product_code' => $row->item_name ?? "",
                    'product_image' => $product->product_image,
                    'product_expiry_date' =>$product->product_expiry_date,
                    'brandname' => $product->brand?->name ?? null,
                    'brandid' => $product->brand?->id ?? null,
                    'categoryid' => $product->category?->id ?? null,
                    'categoryname' => $product->category?->name ?? null,
                ];

                $products[] = $productData;
            }

            $this->variationitems = $products;

            
            $this->dispatch('open-modal', id: 'variation-items');

            //dd($this->variationitems);

    }
    
    public function form(Form $form): Form
    {
        return $form
        ->schema([
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
                ->live()
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
                 ->allowHtml()
                // ->options(Product::all()->pluck('product_name', 'id'))
                ->getSearchResultsUsing(fn (string $search, $get): array => (new Saleservice())->getadjustmentproduct($search,$get('warehouse_id')))
               
                ->options(function (): array {
                     $products = [];
                    // Load Single type products
                    $products = Product::select('id', 'product_name', 'product_code', 'product_image')                        ->where('active', true)
                        ->where('product_type', 'Single')
                        ->get()
                        ->toArray(); // Convert to array

                    // Load Variation type products
                    $products_variation_list = Product::select('id', 'product_name', 'product_code', 'product_image','product_expiry_date')
                        ->where('active', true)
                        ->where('product_type', 'Variation')
                        ->get();

                    foreach ($products_variation_list as $product) {
                        $product_variant_data = $product->variationitems()->orderBy('position')->get();

                        $main = $product->product_name ?? "";

                        foreach ($product_variant_data as $row) {
                            $productData = [
                                'id' => $product->id.','.$row->id,
                                'product_name' => $main . "($row->item_name)",
                                'product_code' => $row->item_name ?? "",
                                'product_image' => $product->product_image,
                                'product_expiry_date' =>$product->product_expiry_date
                            ];
                            $products[] = $productData;
                        }
                    }
            
                    return collect($products)
                        ->map(function ($result): array {
                            return [$result['id'] => ProductSearchImage::getOptionString($result)];
                        })
                        ->toArray();
                })
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
                        $newpx = ($px + (($tax / 100) * $px));
                    }else{
                        $newpx = $px;
                    }
                    
                    $px = $newpx;
                    
                    if ($stock < 1) {
                        
                        $this->dispatch('play-sound');
                        
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
                        'display_price' => $product->tax_method == "1" ? ($px + (($tax / 100) * $px)) : $px,
                        'qty' => 1,
                        'total' => $product->tax_method == "1" ? ($px + (($tax / 100) * $px)) : $px,
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
                        Forms\Components\Hidden::make('sale_unit_id'),
                        Forms\Components\Hidden::make('discount'),
                        Forms\Components\Hidden::make('tax_rate'),
                        Forms\Components\Hidden::make('tax'),
                        Forms\Components\Hidden::make('stock')
                        ->dehydrated(false),
                        
                        // ->label('Product')
                        // ->options(Product::all()->pluck('product_name', 'id'))
                        // ->searchable()
                        // ->preload()
                        // ->disableOptionsWhenSelectedInSiblingRepeaterItems()
                        // ->required(),
                        
                        Forms\Components\TextInput::make('product_name')
                        ->columnSpan(2)
                        ->readOnly(),
                        
                        // Forms\Components\Select::make('product_id')
                        // ->label('Product')
                        //  ->options(Product::all()->pluck('product_name', 'id'))
                        //  ->searchable()
                        //  ->preload()
                        // ->visible(fn (string $operation): bool => $operation === 'edit')
                        // ->disabled(),
                        
                        Forms\Components\Hidden::make('unit_price'),

                        Forms\Components\TextInput::make('display_price')
                        ->type("number")
                        ->disabled()
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
                            ->label("Coupon")
                            ->default(0)
                            ->readOnly()
                            ->suffixAction(
                                Action::make("coupon")
                                ->icon('heroicon-m-pencil-square')
                                ->label("Coupon")
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
                                    ->label("Tax")
                                    ->default(0)
                                    ->readOnly()
                                    ->suffixAction(
                                        Action::make("Tax")
                                        ->icon('heroicon-m-pencil-square')
                                        ->label("Tax")
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
                                        //->prefix('CHC')
                                        ->live(true)
                                        ->afterStateUpdated(function (Forms\Get $get, Forms\Set $set) {
                                            self::updateTotals($get, $set);
                                        })
                                        ->default(0),
                                        
                                        Forms\Components\Hidden::make('order_discount_type')
                                        ->default(""),
                                        
                                        Forms\Components\TextInput::make('order_discount_value')
                                        ->label("Discount")
                                        ->default(0)
                                        ->readOnly()
                                        ->suffixAction(
                                            Action::make("Discount")
                                            ->icon('heroicon-m-pencil-square')
                                            ->label("Discount")
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
                                                Forms\Components\Hidden::make('cash_register_id'),
                                                Forms\Components\Hidden::make('biller_id'),
                                                
                                                
                                                //payments
                                                // Forms\Components\Hidden::make('payment.user_id')
                                                // ->default(auth()->user()->id),
                                                // Forms\Components\Hidden::make('payment.amount')
                                                // ->default(0),
                                                // Forms\Components\Hidden::make('payment.change')
                                                // ->default(0),
                                                // Forms\Components\Hidden::make('payment.customer_id'),
                                                // Forms\Components\Hidden::make('payment.paying_method'),
                                                // Forms\Components\Hidden::make('payment.payment_note'),


                                                // Forms\Components\Section::make('')
                                                //     ->description('')
                                                //     ->schema([
                                                //         Forms\Components\Actions::make([
                                                //             Forms\Components\Actions\Action::make('CASH')
                                                //             ->icon('heroicon-m-banknotes')
                                                //             ->color('danger')
                                                //             ->action(function (){
                                                //                 $this->paidby = "CASH";
        
                                                //                 $this->dispatch('play-sound');
                                                //                 $this->dispatch('open-modal', id: 'pay-with-cash');
                                                //             }),
                                                //             Forms\Components\Actions\Action::make('PAYPAL')
                                                //             ->icon('heroicon-m-currency-dollar')
                                                //             ->color('info')
                                                //             ->action(function (){
                                                //                 $this->paidby = "PAYPAL";
        
                                                //                 $this->dispatch('play-sound');
                                                //                 $this->dispatch('open-modal', id: 'pay-with-cash');
                                                //             }),
                                                //             Forms\Components\Actions\Action::make('CHEQUE')
                                                //             ->icon('heroicon-m-newspaper')
                                                //             ->color('warning')
                                                //             ->action(function (){
                                                //                 $this->paidby = "CHEQUE";
        
                                                //                 $this->dispatch('play-sound');
                                                //                 $this->dispatch('open-modal', id: 'pay-with-cash');
                                                //             })
                                                //         ]),
                                                // ])
                                                // ->columnSpanFull(),
                                                
                                                
                                                
                                                ])
                                                ->columns(4),
                                                ])
                                                ->statePath('data')
                                                ->model(Sales::class);
                                            }
                                            
                                            public static function calculateitems(Forms\Set $set,Forms\Get $get) {
                                                
                                                $state = $get('items');
                                                
                                                $totitem = count($state);
                                                $set("item",$totitem);
                                                
                                                $totalqty = collect($state)
                                                ->pluck('qty')
                                                ->sum();
                                                
                                                $set("total_qty",$totalqty);
                                                
                                            }

                                             public function calculateitemsmanual() {
                                                
                                                $state = $this->data["items"];
                                                
                                                $totitem = count($state);
                                                $this->data["item"] = $totitem;
                                                
                                                $totalqty = collect($state)
                                                ->pluck('qty')
                                                ->sum();
                                                
                                                $this->data["total_qty"] = $totalqty;
                                                
                                            }
                                            
                                            public static function calculateitemsstate($state, $set,$get) {
                                                
                                                $totitem = count($state);
                                                $set("item",$totitem);
                                                
                                                $totalqty = collect($state)
                                                ->pluck('qty')
                                                ->sum();
                                                
                                                $set("total_qty",$totalqty);
                                                
                                            }
                                            
                                            public static function setquantity($productid, $state, $set,$get) {
                                                
                                                static::calculateitems($set,$get);
                                            }
                                            
                                            // This function updates totals based on the selected products and quantities
                                            public function updateTotals(Forms\Get $get, Forms\Set $set): void
                                            {
                                                // Retrieve all selected products and remove empty rows
                                                $selectedProducts = collect($get('items'))->filter(fn($item) => !empty($item['product_id']) && !empty($item['qty']));
                                                
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

                                                $this->received = $grandtotal;
                                                $this->paying = $grandtotal;

                                                static::calculateitems($set,$get);
                                            }


                                            public function updateTotalsmanual(): void
                                            {
                                                // Retrieve all selected products and remove empty rows
                                                $selectedProducts = collect($this->data['items'])->filter(fn($item) => !empty($item['product_id']) && !empty($item['qty']));
                                                
                                                $qty = $selectedProducts->pluck('qty')->sum();
                                                $this->data["total_qty"] = $qty;

                                                $warehouse_id = $this->data["warehouse_id"];
                                                
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
                                                $coupon_type = $this->data["coupon_type"];
                                                
                                                if($coupon_type == "Flat"){
                                                    $coupon_discount = $this->data["coupon_discount"];
                                                }elseif($coupon_type == "Discount"){
                                                    $coupon_discount = ($this->data["coupon_discount"] / 100) * $subtotal;
                                                }else{
                                                    $coupon_discount = $this->data["coupon_discount"];
                                                }
                                                
                                                //add tax
                                                $tax = ($this->data["order_tax_rate"] / 100 ) * $subtotal;
                                                
                                                $this->data["order_tax"] = $tax;
                                                
                                                //add shipping cost
                                                $shipping = $this->data["shipping_cost"];
                                                
                                                //substract discount
                                                $order_discount_type = $this->data["order_discount_type"];
                                                if($order_discount_type == "Flat"){
                                                    $discount = $this->data["order_discount_value"];
                                                }elseif($order_discount_type == "Discount"){
                                                    $discount = ($this->data["order_discount_value"] / 100) * $subtotal;
                                                }else{
                                                    $discount = $this->data["order_discount_value"];
                                                }
                                                
                                                $this->data["total_discount"] = $discount;
                                                
                                                $grandtotal = ($subtotal + $tax + $shipping) -  ($coupon_discount + $discount);
                                                
                                                // Update the state with the new values
                                                $this->data["total_price"] = number_format($subtotal, 2, '.', '');
                                                $this->data["grand_total"] = number_format($grandtotal, 2, '.', '');
                                            
                                                $this->received = $grandtotal;
                                                $this->paying = $grandtotal;
                                            }

                                            public function calculatechange() {
                                                $this->change = abs((int) $this->received - (int) $this->paying);
                                            }
    
    
    public function render()
    {
        return view('livewire.pos.edit-pos-component');
    }
}
