<?php

namespace App\Filament\Cashier\Resources\SalesResource\Pages;

use App\Filament\Cashier\Resources\SalesResource;
use App\Models\Product;
use Filament\Actions;
use Filament\Resources\Pages\CreateRecord;
use Illuminate\Database\Eloquent\Model;
use Filament\Actions\Action;
use Filament\Notifications\Notification;
use App\Models\Coupon;
use App\Models\Payment;
use Filament\Forms;

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
    
    public function getHeading(): string
    {
        return __('');
    }
    
    protected ?string $subheading = '';
    
    protected function getCreatedNotificationTitle(): ?string
    {
        return 'Recorded registered';
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
        $data['biller_id'] = auth()->user()->id;
        $data['reference_number'] = $data['reference_number'] ?? 'posr-' . date("Ymd") . '-'. date("his");
        $data['sale_note'] = $this->salenote;
        $data['staff_note'] = $this->staffnote;
        $data['sale_status'] = 2;
        $data['payment_status'] = 1;
        $data['paid_amount'] = $this->paying;
        
        //payments
        $data['payment']["customer_id"] = $data['customer_id'];
        $data['payment']["amount"] = $this->paying;
        $data['payment']["change"] = $this->change;
        $data['payment']["paying_method"] = $this->paidby;
        $data['payment']["payment_note"] = $this->paymentnote;
        $data['payment']["cheque_no"] = $this->cheque_no;
        $data['payment']["gift_card_id"] = $this->gift_card_id;
        
        //logger($data);
        
        return $data;
    }
    
    
    protected function beforeCreate(): void
    {
        $data = $this->data;
        $items = $this->data['items'] ?? [];
        
        $this->halt();
    }
    
    protected function afterCreate(): void
    {
        $records = $this->getRecord();
        // $data = $this->data;
        
        //update sales status & payment status
        $records->sale_status = 1;
        
        //payment_status
        // Pending - 1,
        // Due - 2,
        // Partial - 3,
        // Paid - 4,
        
        if ($records->paid_amount === $records->grand_total) {
            $records->payment_status = 4;
        }else{
            $records->payment_status = 3;
        }
        
        $records->save();
        
        $saleid = $records->id;
        $points = 0;
        $points +=$records->payment?->used_points;
        
        $data = [
            'used_points' => $points,
            'customer_stripe_id' => '',
            'charge_id' => '',
            'paypal_transaction_id' => '',
        ];
        
        Payment::where('sale_id',$saleid)->update($data);
        
        //check coupon
        $coupon_id = $records->coupon_id;
        
        $coupon = Coupon::where('id',$coupon_id)->first();
        
        if ($coupon) {
            $qty = $coupon->qty;
            $used = (int) $coupon->used++;
            $available = $qty  - $used;
            
            $coupon->available = $available;
            $coupon->save();
        }
        
        //update quantity
        foreach (collect($records->items) as $row) {
            $product = Product::where('id', $row->product_id)->first();
            $avaliableqty = $product->product_qty;
            $subqty = $row->qty;
            $product->product_qty = $avaliableqty - $subqty;
            $product->save();
        }
    }
    
    
    protected function getFormActions(): array
    {
        $this->dispatch('play-sound');
        
        $data = $this->data;
        
        $grand_total = $data["grand_total"];
        $paying_amount = $data["grand_total"];
        
        return [
            // ...parent::getFormActions(),
            Action::make('card')
            ->slideOver()
            ->icon("heroicon-o-credit-card")
            ->label('CREDIT CARD')
            ->modalHeading('Finalise Payment')
            ->modalSubmitActionLabel('Paid with credit card')
            ->fillForm(fn (): array => [
                'received_amount' => $grand_total,
                'paying_amount' => $paying_amount,
                'paid_by' => 'Credit card',
                'change' => 0,
                ])
                ->form([
                    Forms\Components\Section::make('')
                    ->description('')
                    ->schema([
                        Forms\Components\TextInput::make('received_amount')
                        ->live()
                        ->afterStateUpdated(function($state,Forms\Get $get, Forms\Set $set){
                            $set("change", $state - $get("paying_amount"));
                        })
                        ->default(0)
                        ->required(),
                        
                        Forms\Components\TextInput::make('paying_amount')
                        ->live()
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
                            'Cash' => 'Cash',
                            'Credit card' => 'Credit card',
                            'Paypal' => 'Paypal',
                            'Cheque' => 'Cheque',
                            'Gift card' => 'Gift card'
                            ])
                            ->required(),
                            
                            Forms\Components\Textarea::make('payment_note')
                            ->columnSpanFull(),
                            
                            Forms\Components\Textarea::make('sale_note'),
                            
                            Forms\Components\Textarea::make('staff_note'),
                            ])
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
                                
                                $this->create();
                            })
                            ->color('warning'),
                            
                            Action::make('cash')
                            ->icon("heroicon-o-banknotes")
                            ->label('CASH')
                            ->action('cash')
                            ->color('info'),
                            
                            Action::make('paypal')
                            ->icon("heroicon-o-currency-dollar")
                            ->label('PAYPAL')
                            ->action('paypal')
                            ->color('warning'),
                            
                            Action::make('cheque')
                            ->icon("heroicon-o-newspaper")
                            ->label('CHEQUE')
                            ->action('cheque')
                            ->color('danger'),
                            
                            Action::make('giftcard')
                            ->icon("heroicon-o-credit-card")
                            ->label('GIFT CARD')
                            ->action('giftcard')
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
                