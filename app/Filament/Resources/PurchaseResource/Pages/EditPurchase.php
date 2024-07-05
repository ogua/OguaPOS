<?php

namespace App\Filament\Resources\PurchaseResource\Pages;

use App\Filament\Resources\PurchaseResource;
use App\Models\Purchase;
use App\Models\PurchaseItems;
use App\Partials\Enums\PaymentStatus;
use App\Partials\Enums\PurchaseStatus;
use App\Services\Saleservice;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditPurchase extends EditRecord
{
    protected static string $resource = PurchaseResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }

     protected function mutateFormDataBeforeSave(array $data): array
    {
        $payments = collect($this->data["Payments"])->sum('amount');
       
        $left = $data['grand_total'] - $payments;
        $data["paid_amount"] = $payments;
        $data["balance_amount"] = $left;
        $data["user_id"] = auth()->user()->id;

        if ($data['grand_total'] > $payments && $payments > 1) {
            $data["payment_status"] = PaymentStatus::Due;
        }elseif($left < 1){
            $data["payment_status"] = PaymentStatus::Paid;
        }else{
            $data["payment_status"] = PaymentStatus::Pending;
        }

        
        return $data;
    }

    protected function afterSave(): void
    {
        $record = $this->getRecord();
        
        (new Saleservice())->deletepreviousitems($record->id);

        if ($record->purchase_status === PurchaseStatus::Received) {
            (new Saleservice())->updatedstockitems($record->id);
        }
    }

    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('index');
    }
}
