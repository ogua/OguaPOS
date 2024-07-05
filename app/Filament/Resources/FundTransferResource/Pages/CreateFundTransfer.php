<?php

namespace App\Filament\Resources\FundTransferResource\Pages;

use App\Filament\Resources\FundTransferResource;
use App\Models\PaymentAccount;
use Filament\Actions;
use Filament\Resources\Pages\CreateRecord;

class CreateFundTransfer extends CreateRecord
{
    protected static string $resource = FundTransferResource::class;

    protected function getRedirectUrl(): string
    {
        return $this->previousUrl ?? $this->getResource()::getUrl('index');
    }

    protected function mutateFormDataBeforeCreate(array $data): array
    {
        $from = $data['transfer_from'];
        $to = $data['transfer_to'];
        $amount =  $data['amount'];

        $fromacc = PaymentAccount::where('id',$from)->first();
        $bal = $fromacc->current_balance;
        $fromacc->current_balance = $bal - $amount;
        $fromacc->save();

        $toacc = PaymentAccount::where('id',$to)->first();
        $bal = $toacc->current_balance;
        $toacc->current_balance = $bal + $amount;
        $toacc->save();
        
        $data['user_id'] = auth()->user()->id;

        $data['from_balance'] = $fromacc->current_balance;
        $data['to_balance'] = $toacc->current_balance;

        return $data;
    }
}
