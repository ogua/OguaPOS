<?php

namespace App\Filament\Pages\Auth;

use Filament\Forms\Form;
use Filament\Forms;
use Filament\Forms\Components\Component;
use Filament\Forms\Components\Select;
use Filament\Pages\Auth\EditProfile as BaseEditProfile;
use Filament\Forms\Components\TextInput;

class EditProfile extends BaseEditProfile
{
    public function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\FileUpload::make('avatar_url')
                    ->image(),
                $this->getNameFormComponent(),
                $this->getEmailFormComponent(),
                $this->getRoleFormComponent(),
                Forms\Components\TextInput::make('phone')
                    ->required()
                    ->tel(),
                $this->getPasswordFormComponent(),
                $this->getPasswordConfirmationFormComponent(),
            ]);
    }

    protected function getRoleFormComponent(): Component
    {
        return TextInput::make('role')
            ->required()
            ->readOnly();
    }

    protected function getEmailFormComponent(): Component
    {
        return TextInput::make('email')
            ->label('Email / Username')
            ->required()
            ->maxLength(255)
            ->unique(ignoreRecord: true);
    }





}
