<?php

namespace App\Filament\Cashier\Resources\CashregisterResource\Pages;

use App\Filament\Cashier\Resources\CashregisterResource;
use Filament\Actions;
use Filament\Actions\Action;
use Filament\Resources\Pages\CreateRecord;

class CreateCashregister extends CreateRecord
{
    protected static string $resource = CashregisterResource::class;

    protected function getFormActions(): array
    {
        return [
             $this->getCreateFormAction(),
        ];
    }

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
        return "/pos";
    }
}
