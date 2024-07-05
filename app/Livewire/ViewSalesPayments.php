<?php

namespace App\Livewire;

use App\Models\Payment;
use App\Models\PaymentAccount;
use App\Models\Purchase;
use App\Models\PurchaseReturn;
use App\Models\SaleReturn;
use App\Models\Sales;
use App\Partials\Enums\PaymentStatus;
use Livewire\Component;

class ViewSalesPayments extends Component
{
    public $record;
    public $recordtype;

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
                $balance = $paycc->current_balance - $previous;
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
                $balance = $paycc->current_balance - $previous;
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
    
    public function render()
    {
        return view('livewire.view-sales-payments');
    }
}
