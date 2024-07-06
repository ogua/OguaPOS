<?php

namespace App\Filament\Cashier\Resources\PaymentResource\Pages;

use App\Filament\Cashier\Resources\PaymentResource;
use App\Filament\Cashier\Resources\PurchaseResource;
use App\Filament\Cashier\Resources\PurchaseReturnResource;
use App\Filament\Cashier\Resources\SaleReturnResource;
use App\Filament\Cashier\Resources\SalesResource;
use App\Models\PaymentAccount;
use App\Models\Purchase;
use App\Models\PurchaseReturn;
use App\Models\SaleReturn;
use App\Models\Sales;
use App\Partials\Enums\PaymentStatus;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditPayment extends EditRecord
{
    protected static string $resource = PaymentResource::class;

    protected function getHeaderActions(): array
    {
        return [
            
        ];
    }

    public function getTitle() : string 
    {
        return "Edit Payment";   
    }

    protected function getRedirectUrl(): string
    {
        $record = $this->getRecord();

        if ($record->purchase_id)
        {
            return  PurchaseResource::getUrl('index');
        }

        if ($record->sale_id)
        {
           return  SalesResource::getUrl('index');
        }

        if ($record->sale_return_id)
        {
            return  SaleReturnResource::getUrl('index');
        }


        if ($record->purchase_return_id)
        {
            return  PurchaseReturnResource::getUrl('index');
        }
        
    }

    protected function beforeSave(): void
    {
        $record = $this->getRecord();

        $accountid = $record->account_id;
        $previous = $record->amount;
        $now = $this->data["amount"];
        $diff = $now - $previous;

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
            $payment->save();

             //update account.
            $paycc = PaymentAccount::where('id',$accountid)->first();
            if ($paycc) {
                $balance = $paycc->current_balance - $previous;
                $paycc->current_balance = $balance;
                $paycc->save();
            }
        }

    }

    protected function afterSave(): void
    {
        $record = $this->getRecord();

        $accountid = $record->account_id;

        $previous = $record->amount;

        if ($record->purchase_id)
        {
            $payment = Purchase::where('id',$record->purchase_id)->first();
            $total = $payment->grand_total;
            $paid = $payment->paid_amount;
            $bal = $payment->balance_amount;

            $newpaid = $paid + $previous;
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

            $newpaid = $paid + $previous;
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

        if ($record->sale_return_id)
        {
            $payment = SaleReturn::where('id',$record->sale_return_id)->first();
            $total = $payment->total_amount;
            $paid = $payment->amount_paid;
            $bal = $payment->amount_due;

            $newpaid = $paid + $previous;
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

            $newpaid = $paid + $previous;
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
    }
}
