<?php

namespace App\Filament\Resources;

use App\Filament\Resources\CurrencyResource\Pages;
use App\Filament\Resources\CurrencyResource\RelationManagers;
use App\Models\Currency;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class CurrencyResource extends Resource
{
    protected static ?string $model = Currency::class;
    
    protected static ?string $navigationIcon = 'heroicon-o-currency-dollar';
    protected static ?string $slug = 'settings/currency';
    protected static ?string $navigationGroup = 'Settings';
    protected static ?string $navigationLabel = 'Currency';
    protected static ?string $modelLabel = 'Currency';
    protected static ?int $navigationSort = 6;
    
    public static function form(Form $form): Form
    {
        return $form
        ->schema([
            Forms\Components\Section::make('')
            ->description('')
            ->schema([
                Forms\Components\TextInput::make('currency_name')
                ->required()
                ->maxLength(255),
                Forms\Components\TextInput::make('currency_code')
                ->required()
                ->maxLength(255),
                Forms\Components\TextInput::make('exchange_rate')
                ->required()
                ->numeric(),
                ])
                ->columns(2),
            ]);
        }
        
        public static function table(Table $table): Table
        {
            return $table
            ->columns([
                Tables\Columns\TextColumn::make('currency_name')
                ->searchable(),
                Tables\Columns\TextColumn::make('currency_code')
                ->searchable(),
                Tables\Columns\TextColumn::make('exchange_rate')
                ->numeric()
                ->sortable(),
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
                        Tables\Actions\EditAction::make(),
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
                            'index' => Pages\ListCurrencies::route('/'),
                            'create' => Pages\CreateCurrency::route('/create'),
                            'edit' => Pages\EditCurrency::route('/{record}/edit'),
                        ];
                    }
                }
                