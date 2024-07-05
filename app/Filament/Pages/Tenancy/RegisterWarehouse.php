<?php

namespace App\Filament\Pages\Tenancy;

use App\Models\Branches;
use App\Models\Warehouse;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Form;
use Filament\Pages\Tenancy\RegisterTenant;
 
class RegisterWarehouse extends RegisterTenant
{
    public static function getLabel(): string
    {
        return 'Register warehouse';
    }
 
    public function form(Form $form): Form
    {
        return $form
            ->schema([
                TextInput::make('name')
                ->label('Warehouse name / Location'),
            ]);
    }
 
    protected function handleRegistration(array $data): Warehouse
    {
        $team = Warehouse::create($data);
 
        $team->users()->attach(auth()->user());
 
        return $team;
    }
}