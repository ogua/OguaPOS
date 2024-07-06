<?php

namespace App\Filament\Cashier\Resources;

use App\Filament\Cashier\Resources\CashregisterResource\Pages;
use App\Filament\Cashier\Resources\CashregisterResource\RelationManagers;
use App\Models\Cashregister;
use App\Models\Possettings;
use App\Models\Warehouse;
use Carbon\Carbon;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class CashregisterResource extends Resource
{
    protected static ?string $model = Cashregister::class;

    protected static ?string $navigationIcon = 'heroicon-o-banknotes';

    //protected static bool $shouldRegisterNavigation = false;

    protected static ?string $modelLabel = 'Cash Register';

    protected static ?string $navigationLabel = 'Cash Register';

    protected static ?string $slug = 'cash-register';

    // public static function canCreate(): bool
    // {
    //     return false;
    // }


    // public static function getBreadcrumb(): string
    // {
    //     return "";
    // }

    public static function getEloquentQuery(): Builder
    {
        return parent::getEloquentQuery()
        ->where('user_id',auth()->user()->id)
        ->orderBy('id','desc');
    }


    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Section::make('')
                    ->description('')
                    ->schema([
                        
                Forms\Components\TextInput::make('cash_in_hand')
                    ->required()
                    ->numeric(),

                Forms\Components\Hidden::make('user_id')
                    ->default(auth()->user()->id),

                Forms\Components\Select::make('warehouse_id')
                ->label('Warehouse / Shop location')
                ->options(Warehouse::pluck('name','id'))
                ->preload()
                ->searchable()
                ->required(),
                
                Forms\Components\Hidden::make('status')
                ->default(true),

                ])
                ->columns(2),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('cash_in_hand')
                    ->numeric()
                    ->sortable(),

                Tables\Columns\TextColumn::make('warehouse.name')
                ->label('Warehouse')
                ->searchable(),

                Tables\Columns\TextColumn::make('closed_at')
                ->sortable(),

                Tables\Columns\IconColumn::make('status')
                    ->boolean(),

                Tables\Columns\TextColumn::make('created_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),

                Tables\Columns\TextColumn::make('updated_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                //
            ])
            ->actions([
                Tables\Actions\EditAction::make()
                ->hidden(function ($record): bool
                {
                    $date = Carbon::parse($record->closed_at);
                    $now = Carbon::now()->subDay(7);
                    return $date->lessThan($now);

                }),
               // Tables\Actions\DeleteAction::make(),

               Tables\Actions\Action::make('Print')
                ->icon('heroicon-m-receipt-percent')
                ->color('success')
                ->url( fn ($record) => route('print-cash-register', $record->id), shouldOpenInNewTab: true),

            ]);
    }

    public static function getRelations(): array
    {
        return [
            //
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListCashregisters::route('/'),
            'create' => Pages\CreateCashregister::route('/create'),
            'edit' => Pages\EditCashregister::route('/{record}/edit'),
        ];
    }
}
