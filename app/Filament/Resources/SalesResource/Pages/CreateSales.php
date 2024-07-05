<?php

namespace App\Filament\Resources\SalesResource\Pages;

use App\Filament\Resources\SalesResource;
use App\Models\Cashregister;
use App\Models\Coupon;
use App\Models\Giftcard;
use App\Models\Payment;
use App\Models\PaymentAccount;
use App\Models\Possettings;
use App\Models\Product;
use App\Models\Product_Warehouse_Inventory;
use App\Models\Productunit;
use App\Models\Stock_History;
use App\Partials\Enums\PaymentStatus;
use App\Partials\Enums\PurchaseStatus;
use App\Partials\Enums\SalesStatus;
use App\Services\SalesForm;
use Filament\Actions;
use Filament\Actions\Action;
use Filament\Resources\Pages\CreateRecord;
use Filament\Forms;
use Filament\Forms\Components\Livewire as ComponentsLivewire;
use Filament\Notifications\Actions\Action as ActionsAction;
use Filament\Notifications\Notification;
use Laravel\Prompts\ConfirmPrompt;

class CreateSales extends CreateRecord
{
    protected static string $resource = SalesResource::class;
    
    public $received = 0;
    public $paying = 0;
    public $change = 0;
    public $paidby = 0;
    public $paymentnote = "";
    public $salenote = "";
    public $staffnote = "";
    public $cheque_no = "";
    public $gift_card_id = "";
    public $account_id;

    public $bankname = ""; 
    public $accountnumber = "";

    protected function getCreateAnotherFormAction(): Action
    {
        return parent::getCreateAnotherFormAction()
        ->submit(null)
        ->requiresConfirmation()
        ->action(function(){
            $this->closeActionModal();
            $this->create();
        });
    }
    
    
    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('index');
    }

    protected function getSavedNotification(): ?Notification
    {
         $record = $this->getRecord();

        return Notification::make()
            ->success()
            ->title('Sales created')
            ->persistent()
            ->actions([
            ActionsAction::make('print')
            ->url(route('pos-invoice', $record->id), shouldOpenInNewTab: true)
            ->button(),
        ])
        ->body('Sales recorded successfully!.');
    }

    
    //payment_status
    // Pending - 1,
    // Due - 2,
    // Partial - 3,
    // Paid - 4,
    
    //sale_status
    // Completed - 1,
    // Pending - 2,
    
    protected function mutateFormDataBeforeCreate(array $data): array
    {
        $pos = Possettings::where('warehouse_id',$data["warehouse_id"])->first();

        $cashreg = Cashregister::where('user_id',auth()->user()->id)
        ->where('status',true)
        ->where('warehouse_id',$data["warehouse_id"])->first();

        $data["sale_status"] = SalesStatus::Pending;
        $data["payment_status"] = PaymentStatus::Pending;
        $data["currency_id"] = $pos->currency;
        $data["biller_id"] = $pos->biller_id;
        $data["paid_amount"] = $this->received;
        $data["balance_amount"] = (int) $this->paying - (int) $this->received;
        $data["cash_register_id"] = $cashreg->id;
        $data["sale_note"] = $this->salenote;
        $data["staff_note"] = $this->staffnote;
        $data["payment_note"] = $this->paymentnote;
        $data["reference_number"] = blank($data["reference_number"]) ? "POSR-".date('Ymd')."-".date('hms') : $data["reference_number"];
        
        if($this->paidby == "CREDIT SALES"){
            $data["sales_type"] = "CREDIT SALES";
        }
        
        return $data;
    }
    
    protected function beforeCreate(): void
    {
        $data = $this->data;
        $items = $this->data['items'] ?? [];
        
    }
    
    protected function afterCreate(): void
    {
        $records = $this->getRecord();
        // $data = $this->data;

        //debit payment account
        $amount = $records->paid_amount;
        $account = PaymentAccount::where('id',$this->account_id)->first();

        if ($account) {
            $account->current_balance += $amount;
            $account->save();
        }
        

        $paymentinsert = [
            'user_id' => $this->data["user_id"],
            'sale_id' => $records->id,
            'cash_register_id' => $records->cash_register_id,
            'account_id' => $this->account_id,
            'amount' => $records->paid_amount,
            'used_points' => 0,
            'change' => $this->change,
            'cheque_no' => $this->cheque_no,
            'customer_id' => $this->data["customer_id"],
            'customer_stripe_id' => null,
            'charge_id' => null,
            'gift_card_id' => (int) $this->gift_card_id ?? null,
            'paypal_transaction_id' => null,
            'paying_method' => $this->paidby,
            'payment_note' => $this->paymentnote,
            'bankname' => $this->bankname,
            'accountnumber' => $this->accountnumber,
            'payment_type' => "debit",
            'payment_ref' => "SPP-".date('Ymd')."-".date('hms'),
            'paying_type' => "Sales",
            'paid_on' => now(),
            'balance' => $account->current_balance ?? 0
        ];

        $paymentdata = new Payment($paymentinsert);
        $paymentdata->save();


        //check if giftcard
        if ($this->gift_card_id) {

            $paying = $this->paying;

            $card = Giftcard::where('id',$this->gift_card_id)->first();

            $expense = $card->expense + $paying;

            $card->expense = $expense;
            $card->save();
        }

        //check coupon
        $coupon_id = $records->coupon_id;
        
        $coupon = Coupon::where('id',$coupon_id)->first();
        
        if ($coupon) {
            $qty = $coupon->qty;
            $used = (int) $coupon->used + 1;
            $available = $qty  - $used;
            
            $coupon->available = $available;
            $coupon->save();
        }


        $records->sale_status = SalesStatus::Completed;
        if($records->paid_amount == "0"){
            $records->payment_status = PaymentStatus::Due;
        }elseif($records->paid_amount < $this->paying){
            $records->payment_status = PaymentStatus::Partial;
        }else{

            if($this->paidby == "CREDIT SALES"){
                $records->sales_type = "CREDIT SALES PAID";
            }
            
            $records->payment_status = PaymentStatus::Paid;
        }
        //$records->payment_status = $records->paid_amount < $this->paying ? PaymentStatus::Partial : PaymentStatus::Paid;
        $records->save();
        
        //update quantity
        foreach (collect($records->saleitem) as $row) {

            $product = Product::where('id', $row->product_id)->first();

            $product_type = $product->product_type;
            $warehouse = $records->warehouse_id;

            $unit = Productunit::where("id",$row->sale_unit_id)->first();

            if ($unit->base_unit) {
                $qty = (int) $unit->operation_value * $row->qty;
            }else{
                $qty = $row->qty;
            }

            if($product_type == "Single"){

                $stock = Product_Warehouse_Inventory::where('product_id',$product->id)
                ->where('warehouse_id',$warehouse)
                ->first();

                $totalqty = $stock->qty - $qty;
                $stock->qty = $totalqty;
                $stock->save();

                //update history
                $avaliableqty = $stock->qty;

                //update stock from warehouse history
                $stockout = [
                    'product_id' => $product->id,
                    'warehouse_id' => $warehouse,
                    'adjustment_item_id' => $records->id,
                    'type' => 'Sales',
                    'qty_change' => "-".$qty,
                    'new_quantity' => $avaliableqty,
                    'date' => now(),
                    'reference' => $records->reference_number
                ];
                
                Stock_History::create($stockout);

            }elseif ($product_type == "Variation"){

                $stock = Product_Warehouse_Inventory::where('product_id',$product->id)
                ->where('warehouse_id',$warehouse)
                ->where('variant_id',$row->variant_id)
                ->first();

                $totalqty = $stock->qty - $qty;
                $stock->qty = $totalqty;
                $stock->save();

                //update history
                $avaliableqty = $stock->qty;

                //update stock from warehouse history
                $stockout = [
                    'product_id' => $product->id,
                    'warehouse_id' => $warehouse,
                    'variant_id' => $row->variant_id,
                    'adjustment_item_id' => $records->id,
                    'type' => 'Sales',
                    'qty_change' => "-".$qty,
                    'new_quantity' => $avaliableqty,
                    'date' => $records->created_at,
                    'reference' => $records->reference_number
                ];
                
                Stock_History::create($stockout); 

            }

        }

    }
    
    
    protected function getFormActions(): array
    {
        $data = $this->data;
        
        $grand_total = $data["grand_total"];
        $paying_amount = $data["grand_total"];
        
        return [
            Action::make('card')
            ->slideOver()
            ->icon("heroicon-o-credit-card")
            ->label('CREDIT CARD')
            ->modalHeading('Finalise Payment')
            ->modalSubmitActionLabel('Paid with credit card')
            ->fillForm(function () : array {
                
                $this->dispatch('play-sound');
                
                return [
                    'received_amount' => $this->data["grand_total"],
                    'paying_amount' => $this->data["grand_total"],
                    'paid_by' => 'CREDIT CARD',
                    'change' => 0,
                ];
            })
            ->form([
                Forms\Components\Section::make('')
                ->description('')
                ->schema(SalesForm::schema("Card"))
                ->columns(2),
                ])
                ->action(function(array $data){
                    
                    $this->received = $data['received_amount'];
                    $this->paying = $data['paying_amount'];
                    $this->change = $data['change'];
                    $this->paidby = $data['paid_by'];
                    $this->paymentnote = $data['payment_note'];
                    $this->salenote = $data['sale_note'];
                    $this->staffnote = $data['staff_note'];
                    $this->account_id = $data["account_id"];
                    
                    $this->card();
                })
                ->color('success'),

                Action::make('credit')
                ->slideOver()
                ->icon("heroicon-o-currency-dollar")
                ->label('CREDIT SALES')
                ->requiresConfirmation()
                ->action(function(array $data){
                    
                    $this->received = 0;
                    $this->paying = $this->data["grand_total"];
                    $this->change = 0;
                    $this->paidby = "CREDIT SALES";
                    $this->paymentnote = null;
                    $this->salenote = null;
                    $this->staffnote = null;
                    $this->account_id = $data["account_id"];
                    
                    $this->card();
                })
                ->color('warning'),
                
                // Action::make('paypal')
                // ->slideOver()
                // ->icon("heroicon-o-currency-dollar")
                // ->label('PAYPAL')
                // ->modalHeading('Finalise Payment')
                // ->modalSubmitActionLabel('Paid with paypal')
                // ->fillForm(function () : array {
                    
                //     $this->dispatch('play-sound');
                    
                //     return [
                //         'received_amount' => $this->data["grand_total"],
                //         'paying_amount' => $this->data["grand_total"],
                //         'paid_by' => 'PAYPAL',
                //         'change' => 0,
                //     ];
                // })
                // ->form([
                //     Forms\Components\Section::make('')
                //     ->description('')
                //     ->schema(SalesForm::schema("paypal"))
                //     ->columns(2),
                //     ])
                //     ->action(function(array $data){
                        
                //         $this->received = $data['received_amount'];
                //         $this->paying = $data['paying_amount'];
                //         $this->change = $data['change'];
                //         $this->paidby = $data['paid_by'];
                //         $this->paymentnote = $data['payment_note'];
                //         $this->salenote = $data['sale_note'];
                //         $this->staffnote = $data['staff_note'];
                        
                //         $this->card();
                //     })
                //     ->color('warning'),
                    
                    Action::make('cash')
                    ->slideOver()
                    ->icon("heroicon-o-credit-card")
                    ->label('CASH')
                    ->modalHeading('Finalise Payment')
                    ->modalSubmitActionLabel('Paid with cash')
                    ->fillForm(function () : array {
                        
                        $this->dispatch('play-sound');
                        
                        return [
                            'received_amount' => $this->data["grand_total"],
                            'paying_amount' => $this->data["grand_total"],
                            'paid_by' => 'CASH',
                            'change' => 0,
                        ];
                    })
                    ->form([
                        Forms\Components\Section::make('')
                        ->description('')
                        ->schema(SalesForm::schema("Cash"))
                        ->columns(2),
                        ])
                        ->action(function(array $data){
                            
                            $this->received = $data['received_amount'];
                            $this->paying = $data['paying_amount'];
                            $this->change = $data['change'];
                            $this->paidby = $data['paid_by'];
                            $this->paymentnote = $data['payment_note'];
                            $this->salenote = $data['sale_note'];
                            $this->staffnote = $data['staff_note'];
                            $this->account_id = $data["account_id"];
                            
                            $this->card();
                        })
                        ->color('info'),
                        
                        
                        Action::make('cheque')
                        ->slideOver()
                        ->icon("heroicon-o-newspaper")
                        ->label('CHEQUE')
                        ->modalHeading('Finalise Payment')
                        ->modalSubmitActionLabel('Paid with cheque')
                        ->fillForm(function () : array {
                            
                            $this->dispatch('play-sound');
                            
                            return [
                                'received_amount' => $this->data["grand_total"],
                                'paying_amount' => $this->data["grand_total"],
                                'paid_by' => 'CHEQUE',
                                'change' => 0,
                            ];
                        })
                        ->form([
                            Forms\Components\Section::make('')
                            ->description('')
                            ->schema(SalesForm::schema("cheque"))
                            ->columns(2),
                            ])
                            ->action(function(array $data){
                                
                                $this->received = $data['received_amount'];
                                $this->paying = $data['paying_amount'];
                                $this->change = $data['change'];
                                $this->paidby = $data['paid_by'];
                                $this->paymentnote = $data['payment_note'];
                                $this->salenote = $data['sale_note'];
                                $this->staffnote = $data['staff_note'];
                                $this->cheque_no = $data['cheque_no'];
                                $this->account_id = $data["account_id"];
                                
                                $this->card();
                            })
                            ->color('danger'),
                            
                            Action::make('giftcard')
                            ->slideOver()
                            ->icon("heroicon-o-credit-card")
                            ->label('GIFT CARD')
                            ->modalHeading('Finalise Payment')
                            ->modalSubmitActionLabel('Paid with gift card')
                            ->fillForm(function () : array {
                                
                                $this->dispatch('play-sound');
                                
                                return [
                                    'received_amount' => $this->data["grand_total"],
                                    'paying_amount' => $this->data["grand_total"],
                                    'paid_by' => 'GIFT CARD',
                                    'change' => 0,
                                ];
                            })
                            ->form([
                                Forms\Components\Section::make('')
                                ->description('')
                                ->schema(SalesForm::schema("giftcard"))
                                ->columns(2),
                                ])
                                ->action(function(array $data){
                                    
                                    $this->received = $data['received_amount'];
                                    $this->paying = $data['paying_amount'];
                                    $this->change = $data['change'];
                                    $this->paidby = $data['paid_by'];
                                    $this->paymentnote = $data['payment_note'];
                                    $this->salenote = $data['sale_note'];
                                    $this->staffnote = $data['staff_note'];
                                    $this->gift_card_id = $data['gift_card_id'];
                                    $this->account_id = $data["account_id"];
                                    
                                    $this->card();
                                })
                                ->color('success'),
                                
                                Action::make('draft')
                                ->slideOver()
                                ->icon("heroicon-o-newspaper")
                                ->label('DRAFT')
                                ->modalHeading('Finalise Payment')
                                ->modalSubmitActionLabel('Save as draft')
                                ->fillForm(function () : array {
                                    
                                    $this->dispatch('play-sound');
                                    
                                    return [
                                        'received_amount' => $this->data["grand_total"],
                                        'paying_amount' => $this->data["grand_total"],
                                        'paid_by' => 'DRAFT',
                                        'change' => 0,
                                    ];
                                })
                                ->form([
                                    Forms\Components\Section::make('')
                                    ->description('')
                                    ->schema(SalesForm::schema("draft"))
                                    ->columns(2),
                                    ])
                                    ->action(function(array $data){
                                        
                                        $this->received = $data['received_amount'];
                                        $this->paying = $data['paying_amount'];
                                        $this->change = $data['change'];
                                        $this->paidby = $data['paid_by'];
                                        $this->paymentnote = $data['payment_note'];
                                        $this->salenote = $data['sale_note'];
                                        $this->staffnote = $data['staff_note'];
                                        $this->account_id = $data["account_id"];
                                        
                                        $this->card();
                                    })
                                    ->color('info'),
                                    
                                    
                                    Action::make('bank')
                                    ->slideOver()
                                    ->icon("heroicon-o-home-modern")
                                    ->label('Bank Transfer')
                                    ->modalHeading('Finalise Payment')
                                    ->modalSubmitActionLabel('Paid with at bank')
                                    ->fillForm(function () : array {
                                        
                                        $this->dispatch('play-sound');
                                        
                                        return [
                                            'received_amount' => $this->data["grand_total"],
                                            'paying_amount' => $this->data["grand_total"],
                                            'paid_by' => 'BANK TRANSFER',
                                            'change' => 0,
                                        ];
                                    })
                                    ->form([
                                        Forms\Components\Section::make('')
                                        ->description('')
                                        ->schema(SalesForm::schema("bank"))
                                        ->columns(2),
                                        ])
                                        ->action(function(array $data){
                                            
                                            $this->received = $data['received_amount'];
                                            $this->paying = $data['paying_amount'];
                                            $this->change = $data['change'];
                                            $this->paidby = $data['paid_by'];
                                            $this->paymentnote = $data['payment_note'];
                                            $this->salenote = $data['sale_note'];
                                            $this->staffnote = $data['staff_note'];

                                            $this->staffnote = $data['bankname'];
                                            $this->gift_card_id = $data['accountnumber'];
                                            $this->account_id = $data["account_id"];
                                            
                                            $this->card();
                                        })
                                        ->color('success'),
                                        
                                        $this->getCancelFormAction(),
                                    ];
                                }
                                
                                public function card() : void
                                {                        
                                    
                                    $this->create();
                                    //$resources = static::getResource();
                                    //$this->redirect($resources::getUrl('create'));
                                }
                                
                                
                            }
                            