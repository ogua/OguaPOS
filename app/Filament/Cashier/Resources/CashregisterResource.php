<?php

namespace App\Filament\Cashier\Resources;

use App\Filament\Cashier\Resources\CashregisterResource\Pages;
use App\Filament\Cashier\Resources\CashregisterResource\RelationManagers;
use App\Models\Cashregister;
use App\Models\Possettings;
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

                Forms\Components\Hidden::make('warehouse_id')
                    ->default(Possettings::where('status',true)->latest()->first()->warehouse_id),
                
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

                Tables\Columns\TextColumn::make('user_id')
                    ->numeric()
                    ->sortable(),

                Tables\Columns\TextColumn::make('warehouse_id')
                    ->numeric()
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
               // Tables\Actions\EditAction::make(),
                Tables\Actions\DeleteAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                ]),
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
