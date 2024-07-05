<?php

namespace App\Filament\Resources\PurchaseResource\Pages;

use App\Filament\Resources\PurchaseResource;
use App\Models\Payment;
use App\Models\PaymentAccount;
use App\Partials\Enums\PaymentStatus;
use App\Partials\Enums\PurchaseStatus;
use App\Services\Saleservice;
use Filament\Actions;
use Filament\Resources\Pages\CreateRecord;
use Illuminate\Support\Facades\DB;

class CreatePurchase extends CreateRecord
{
    protected static string $resource = PurchaseResource::class;
    
    protected function mutateFormDataBeforeCreate(array $data): array
    {
        $payments = collect($this->data["Payments"])->sum('amount');
        
        if ($data['reference_no'] == "") {
            
            $maxcode = DB::table('purchases')
            ->where('reference_no','like','%PO%')
            ->latest()
            ->first();
            
            if ($maxcode) {
                $max = substr($maxcode->reference_no, 7);
                $number = $max + 1;
                
                if (strlen($number) === 1) {
                    $code = "PO".date('Y')."/000".$number;
                }elseif (strlen($number) === 2) {
                    $code = "PO".date('Y')."/00".$number;
                }elseif (strlen($number) === 3) {
                    $code = "PO".date('Y')."/0".$number;
                }else{
                    $code = "PO".date('Y')."/".$number;
                }
                
            }else{
                $code = "PO".date('Y')."/0001";
            }
        }

        $data['reference_no'] = $code;
        $left = $data['grand_total'] - $payments;
        $data["paid_amount"] = $payments;
        $data["balance_amount"] = $left;
        $data["user_id"] = auth()->user()->id;

        if ($data['grand_total'] > $payments && $payments > 1) {
            $data["payment_status"] =PaymentStatus::Due;
        }elseif($left < 1){
            $data["payment_status"] =PaymentStatus::Paid;
        }else{
            $data["payment_status"] =PaymentStatus::Pending;
        }

        
        return $data;
    }
    
    protected function afterCreate(): void
    {
        $record = $this->getRecord();
        $id = $record->id;

        foreach ($record->Payments as $Payment) {

            $amount = $Payment->amount;
            $accid = $Payment->account_id;

            $account = PaymentAccount::where('id',$accid)->first();

            if ($account) {
                $account->current_balance -= $amount;
                $account->save();
            }

            $Payment->payment_type = "credit";
            $Payment->payment_ref = "SPP-".date('Ymd')."-".date('hms');
            $Payment->paying_type = "Purchase";
            $Payment->customer_id = $record->customer_id;
            $Payment->balance = $account->current_balance ?? 0;
            $Payment->save();
        }

        //Payment::where('purchase_id',$id)->update(['customer_id' => $record->customer_id]);
        
        if ($record->purchase_status === PurchaseStatus::Received) {
            (new Saleservice())->createdstockitems($record->id);
        }
    }
    
    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('index');
    }
}
