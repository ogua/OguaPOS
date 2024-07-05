<?php

namespace App\Livewire;

use Livewire\Component;
use App\Models\Coupon;
use App\Models\Giftcard;
use App\Models\Payment;
use App\Models\PaymentAccount;
use App\Models\Purchase;
use App\Models\Sales;
use App\Partials\Enums\PaymentStatus;
use App\Partials\Enums\SalesStatus;
use Filament\Notifications\Notification;

class UpdatePurchasePayment extends Component
{
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
    public $filtercategory = "";
    public $filterbrand = "";
    public $payby = ""; 
    public $bankname = ""; 
    public $accountnumber = "";

    public $payingaccount;
    public $account_id = "";

    public $record;


    public function mount($record) {

        $this->received = 0;
        $this->paying = $record->balance_amount;
        $this->change = 0;
        //$this->paidby = $record->payment?->paying_method ?? "";
        $this->bankname = $record->payment?->bankname ?? "";
        $this->accountnumber = $record->payment?->accountnumber ?? "";
        $this->cheque_no = $record->payment?->cheque_no ?? "";
        $this->gift_card_id = $record->payment?->gift_card_id ?? "";
        $this->gift_card = $record->payment?->gift_card ?? "";
        $this->payment_note = $record->payment?->payment_note ?? "";
        $this->sale_note = $record->sale_note ?? "";
        $this->staffnote = $record->staff_note ?? "";
        $this->payingaccount = PaymentAccount::latest()->get(); 
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

    public function calculatechange() {
        $this->change = abs((int) $this->received - (int) $this->paying);
    }

    public function changepayment() {
        //$this->change = abs((int) $this->received - (int) $this->paying);
    }


    public function deletepayment($id) {
        $payment = Payment::findoffail($id);
        $amount = $payment->amount;

        $sale = Purchase::where('id',$payment->sale_id)->first();
        $paid = $sale->paid_amount - $amount;
        $grand = $sale->grand_total;
        $bal =  $grand - $paid;
       // $sale->grand_total = $this->paying;
        $sale->paid_amount = $paid;
        $sale->balance_amount = $bal;

        $payment->delete();
    }

    public function updatepayment() {

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

            $this->dispatch('play-error');

            Notification::make()
            ->title('Paypal Checkout coming soon!')
            ->warning()
            ->send();

            return;
        }


        $paymentinsert = [
            'account_id' => $this->account_id,
            'amount' => $this->received,
            'used_points' => null,
            'change' => $this->change,
            'cheque_no' => $this->cheque_no,
            'customer_stripe_id' => null,
            'charge_id' => null,
            'gift_card_id' => $this->paidby == "GIFT CARD" ? (int) $this->gift_card_id : null,
            'paypal_transaction_id' => null,
            'paying_method' => $this->paidby,
            'payment_note' => $this->payment_note,
            'bankname' => $this->bankname,
            'accountnumber' => $this->accountnumber,
            'purchase_id' => $this->record->id
        ];

        $new = new Payment($paymentinsert);
        $new->save();

        // Update gift card
        if ($this->paidby == "GIFT CARD") {

            $previouspaid = $this->record->paid_amount;
            $nowpaying = $this->received;
            $diff = $nowpaying - $previouspaid;

            $giftCard = Giftcard::find($this->record->gift_card_id);

            if ($this->record->gift_card_id && $this->record->payment?->paying_method == "GIFT CARD") {

                if($diff > 0){
                    $giftCard->expense += $diff;

                }elseif($diff < 0){
                    $giftCard->expense -= abs($diff);
                }
            }else{
                $giftCard->expense += $this->received;
            }
            
            $giftCard->save();
        }

        $sale = Purchase::where('id',$this->record->id)->first();
        $paid = $sale->paid_amount + $this->received;
        $grand = $sale->grand_total;
        $bal =  $grand - $paid;
       // $sale->grand_total = $this->paying;
        $sale->paid_amount = $paid;
        $sale->balance_amount = $bal;
        $sale->sale_note = $this->sale_note;
        $sale->staff_note = $this->staffnote;

        if($bal > 0){
            $sale->payment_status = PaymentStatus::Partial;
        }else{
            $sale->payment_status = PaymentStatus::Paid;

            if($sale->sales_type == "CREDIT SALES"){
                $sale->sales_type = "CREDIT SALES PAID";
            }
        }

        //$sale->payment_status = $this->received < $this->paying ? PaymentStatus::Partial : PaymentStatus::Paid;
        $sale->save();

        Notification::make()
        ->title('Updated successfully!')
        ->success()
        ->body('Sales payment updated successfully!.')
        ->send();
        
    }

    public function render()
    {
        return view('livewire.update-purchase-payment');
    }
}
