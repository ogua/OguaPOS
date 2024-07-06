<?php

namespace App\Filament\Pages\Auth;

use Filament\Forms\Form;
use Filament\Forms;
use Filament\Forms\Components\Component;
use Filament\Pages\Auth\Login;
use Filament\Forms\Components\TextInput;
use Filament\Actions\Action;
use Filament\Actions\ActionGroup;
use Illuminate\Contracts\Support\Htmlable;

class CashierLogin extends Login
{
   /**
     * @return array<int | string, string | Form>
     */
    protected function getForms(): array
    {
        return [
            'form' => $this->form(
                $this->makeForm()
                    ->schema([
                        $this->getEmailFormComponent(),
                        $this->getPasswordFormComponent(),
                        $this->getRememberFormComponent(),
                    ])
                    ->statePath('data'),
            ),
        ];
    }

    protected function getEmailFormComponent(): Component
    {
        return TextInput::make('email')
            ->label('Email address / Username')
            ->required()
            ->autocomplete()
            ->autofocus()
            ->extraInputAttributes(['tabindex' => 1]);
    }

     /**
     * @return array<Action | ActionGroup>
     */
    protected function getFormActions(): array
    {
        return [
            $this->getAuthenticateFormAction(),
           // $this->getAdminFormAction(),
        ];
    }

    protected function getAdminFormAction(): Action
    {
        return Action::make('admin')
            ->label('admin login')
            ->color('info')
            ->url('/admin');
    }

    public function getTitle(): string | Htmlable
    {
        return __('filament-panels::pages/auth/login.title');
    }

    public function getHeading(): string | Htmlable
    {
        return __('Cashier Login');
    }




}
