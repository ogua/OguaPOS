<?php

namespace App\Livewire;

use App\Models\Coupon;
use App\Models\Giftcard;
use App\Models\Payment;
use App\Models\PaymentAccount;
use App\Models\Purchase;
use App\Models\PurchaseReturn;
use App\Models\SaleReturn;
use App\Models\Sales;
use App\Partials\Enums\PaymentStatus;
use App\Partials\Enums\SalesStatus;
use Livewire\Component;
use Filament\Notifications\Notification;

class UpdateSalesPayment extends Component
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
    public $recordtype;


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
        $record = Payment::where('id',$id)->first();

        dd($record);

        $previous = $record->amount;
        $accountid = $record->account_id;

        if ($record->purchase_id)
        {
            $payment = Purchase::where('id',$record->purchase_id)->first();
            $total = $payment->grand_total;
            $paid = $payment->paid_amount;
            $bal = $payment->balance_amount;

            $newpaid = $paid - $previous;
            $newbalnace = $total - $newpaid;

            $payment->paid_amount = $newpaid;
            $payment->balance_amount = $newbalnace;

            if ($newbalnace > 0) {
                $payment->payment_status = PaymentStatus::Partial;
            }else {
                 $payment->payment_status = PaymentStatus::Paid;
            }

            $payment->save();

             //update account.
            $paycc = PaymentAccount::where('id',$accountid)->first();
            if ($paycc) {
                $balance = $paycc->current_balance + $previous;
                $paycc->current_balance = $balance;
                $paycc->save();
            }

        }

        if ($record->sale_id)
        {
            $payment = Sales::where('id',$record->sale_id)->first();
            $total = $payment->grand_total;
            $paid = $payment->paid_amount;
            $bal = $payment->balance_amount;

            $newpaid = $paid - $previous;
            $newbalnace = $total - $newpaid;

            $payment->paid_amount = $newpaid;
            $payment->balance_amount = $newbalnace;
            
            if ($newbalnace > 0) {
                $payment->payment_status = PaymentStatus::Partial;
            }else {
                 $payment->payment_status = PaymentStatus::Paid;
            }

            $payment->save();

             //update account.
            $paycc = PaymentAccount::where('id',$accountid)->first();
            if ($paycc) {
                $balance = $paycc->current_balance - $previous;
                $paycc->current_balance = $balance;
                $paycc->save();
            }
        }

        if ($record->sale_return_id)
        {
            $payment = SaleReturn::where('id',$record->sale_return_id)->first();
            $total = $payment->total_amount;
            $paid = $payment->amount_paid;
            $bal = $payment->amount_due;

            $newpaid = $paid - $previous;
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
                $balance = $paycc->current_balance + $previous;
                $paycc->current_balance = $balance;
                $paycc->save();
            }
        }


        if ($record->purchase_return_id)
        {
            $payment = PurchaseReturn::where('id',$record->purchase_return_id)->first();
            $total = $payment->total_amount;
            $paid = $payment->amount_paid;
            $bal = $payment->amount_due;

            $newpaid = $paid - $previous;
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
                $balance = $paycc->current_balance - $previous;
                $paycc->current_balance = $balance;
                $paycc->save();
            }
        }


        $payment->delete();
        
    }

    public function updatepayment() {

        $this->validate([ 
            'received' => 'required',
            'paying' => 'required',
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


        //debit payment account
        $amount = $this->received;
        $account = PaymentAccount::where('id',$this->account_id)->first();

        if ($account) {
            $account->current_balance += $amount;
            $account->save();
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
            'sale_id' => $this->record->id,
            'payment_type' => "debit",
            'payment_ref' => "SPP-".date('Ymd')."-".date('hms'),
            'paying_type' => "Sales",
            'paid_on' => now(),
            'balance' => $account->current_balance ?? 0
        ];

        $new = new Payment($paymentinsert);
        $new->save();

        $accountid = $this->account_id;

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

        if ($this->recordtype == "Purchase")
        {
            $payment = Purchase::where('id',$this->record->id)->first();
            $total = $payment->grand_total;
            $paid = $payment->paid_amount;
            $bal = $payment->balance_amount;

            $newpaid = $paid + $this->received;
            $newbalnace = $total - $newpaid;

            $payment->paid_amount = $newpaid;
            $payment->balance_amount = $newbalnace;

            if ($newbalnace > 0) {
                $payment->payment_status = PaymentStatus::Partial;
            }else {
                 $payment->payment_status = PaymentStatus::Paid;
            }

            $payment->save();

             //update account.
            $paycc = PaymentAccount::where('id',$accountid)->first();
            if ($paycc) {
                $balance = $paycc->current_balance - $this->received;
                $paycc->current_balance = $balance;
                $paycc->save();
            }

        }

        if ($this->recordtype == "Sales")
        {
            $payment = Sales::where('id',$this->record->id)->first();
            $total = $payment->grand_total;
            $paid = $payment->paid_amount;
            $bal = $payment->balance_amount;

            $newpaid = $paid - $this->received;
            $newbalnace = $total - $newpaid;

            $payment->paid_amount = $newpaid;
            $payment->balance_amount = $newbalnace;
            
            if ($newbalnace > 0) {
                $payment->payment_status = PaymentStatus::Partial;
            }else {
                 $payment->payment_status = PaymentStatus::Paid;

                 if($payment->sales_type == "CREDIT SALES"){
                    $payment->sales_type = "CREDIT SALES PAID";
                }
            }

            $payment->save();

             //update account.
            $paycc = PaymentAccount::where('id',$accountid)->first();
            if ($paycc) {
                $balance = $paycc->current_balance - $this->record->id;
                $paycc->current_balance = $balance;
                $paycc->save();
            }
        }

        if ($this->recordtype == "Sales return")
        {
            $payment = SaleReturn::where('id',$this->record->id)->first();
            $total = $payment->total_amount;
            $paid = $payment->amount_paid;
            $bal = $payment->amount_due;

            $newpaid = $paid - $this->record->id;
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
                $balance = $paycc->current_balance - $this->record->id;
                $paycc->current_balance = $balance;
                $paycc->save();
            }
        }


        if ($this->recordtype == "Purchase return")
        {
            $payment = PurchaseReturn::where('id',$this->record->id)->first();
            $total = $payment->total_amount;
            $paid = $payment->amount_paid;
            $bal = $payment->amount_due;

            $newpaid = $paid - $this->record->id;
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
                $balance = $paycc->current_balance - $this->record->id;
                $paycc->current_balance = $balance;
                $paycc->save();
            }
        }

        $sale = Sales::where('id',$this->record->id)->first();
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

        $sale->save();

        Notification::make()
        ->title('Updated successfully!')
        ->success()
        ->body('Sales payment updated successfully!.')
        ->send();
        
    }

    public function render()
    {
        return view('livewire.update-sales-payment');
    }
}
