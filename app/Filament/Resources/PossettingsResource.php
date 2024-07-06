<?php

namespace App\Filament\Resources;

use App\Filament\Resources\PossettingsResource\Pages;
use App\Filament\Resources\PossettingsResource\RelationManagers;
use App\Models\Possettings;
use App\Models\Clients;
use App\Models\Companyinfo;
use App\Models\Warehouse;
use App\Models\Currency;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class PossettingsResource extends Resource
{
    protected static ?string $model = Possettings::class;

    protected static ?string $navigationIcon = 'heroicon-o-printer';
    protected static ?string $slug = 'settings/pos-settings';
    protected static ?string $navigationGroup = 'Settings';
    protected static ?string $navigationLabel = 'Pos Settings';
    protected static ?string $modelLabel = 'Pos Settings';
    protected static ?int $navigationSort = 7;

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Section::make('')
                    ->description('')
                    ->schema([
                Forms\Components\Select::make('customer_id')
                    ->label('Default Customer')
                    ->required()
                    ->options(Clients::where('is_active', true)->pluck('name','id'))
                    ->preload()
                    ->searchable(),

                Forms\Components\Select::make('warehouse_id')
                    ->label('Default Warehouse')
                    ->required()
                    ->options(Warehouse::where('is_active', true)->pluck('name','id'))
                    ->preload()
                    ->searchable(),

                Forms\Components\Select::make('biller_id')
                    ->label('Biller')
                    ->required()
                    ->options(Companyinfo::pluck('name','id'))
                    ->preload()
                    ->searchable(),

                Forms\Components\Select::make('currency')
                    ->required()
                    ->options(Currency::pluck('currency_name','id'))
                    ->preload()
                    ->searchable(),

                Forms\Components\Select::make('invoice_format')
                    ->required()
                    ->options([
                        'Standard' => 'Standard',
                        'Indian GST' => 'Indian GST'
                    ])
                    ->live()
                    ->default('Standard'),

                Forms\Components\Select::make('state')
                    ->visible(fn ($get): bool => $get('invoice_format') == "Indian GST")
                    ->required()
                    ->options([
                        'Home State' => 'Home State',
                        'Buyer State' => 'Buyer State'
                    ])
                    ->default('Home State'),

                

                Forms\Components\TextInput::make('developed_by')
                    ->required()
                    ->maxLength(255)
                    ->default('Oguses IT Solutions'),

                Forms\Components\Toggle::make('status')
                    ->required(),

                Forms\Components\hidden::make('user_id')
                ->default(auth()->user()->id)

                    
                    ])
                ->columns(2),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('customer.name')
                    ->label('Default Customer')
                    ->sortable(),
                Tables\Columns\TextColumn::make('warehouse.name')
                    ->label("Default Warehouse")
                    ->sortable(),
                Tables\Columns\TextColumn::make('company.name')
                    ->label('Company / Biller')
                    ->sortable(),
                Tables\Columns\TextColumn::make('currncy.currency_name')
                    ->label('Currency')
                    ->sortable(),
                Tables\Columns\TextColumn::make('invoice_format')
                    ->searchable(),
                Tables\Columns\TextColumn::make('developed_by')
                    ->searchable(),
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
                Tables\Actions\EditAction::make(),
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
            'index' => Pages\ListPossettings::route('/'),
            'create' => Pages\CreatePossettings::route('/create'),
            'edit' => Pages\EditPossettings::route('/{record}/edit'),
        ];
    }
}
