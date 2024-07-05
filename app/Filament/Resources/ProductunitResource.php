<?php

namespace App\Filament\Resources;

use App\Filament\Resources\ProductunitResource\Pages;
use App\Filament\Resources\ProductunitResource\RelationManagers;
use App\Models\Productunit;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class ProductunitResource extends Resource
{
    protected static ?string $model = Productunit::class;
    
    protected static ?string $navigationIcon = 'heroicon-o-clipboard-document-list';
    protected static ?string $slug = 'products/unit';
    protected static ?string $navigationGroup = 'Products';
    protected static ?string $navigationLabel = 'Unit';
    protected static ?string $modelLabel = 'Unit';
    protected static ?int $navigationSort = 5;

    public static function getEloquentQuery(): Builder
    {
        return parent::getEloquentQuery()
        ->orderBy('id','desc');
    }
    
    public static function form(Form $form): Form
    {
        return $form
        ->schema([
            Forms\Components\Section::make('')
            ->description('')
            ->schema([
                Forms\Components\TextInput::make('name')
                ->required()
                ->live()
                ->maxLength(255),

                Forms\Components\TextInput::make('code')
                ->label('Short name')
                ->required()
                ->maxLength(255),

                Forms\Components\Toggle::make('operator')
                ->label('Add as a multiple of other units')
                ->helperText('Example 1 Dozen = 12 pieces')
                ->live(),

                Forms\Components\Section::make('')
                    ->description('')
                    ->visible(fn ($get): bool => $get("operator") == "1")
                   // ->visible(fn ($get): bool => $get('product_type') == 'Variation')
                    ->schema([

                        Forms\Components\Placeholder::make('Label')
                        ->label('')
                         ->content(function($get){
                            return "1 ".$get("name")." = ";
                         }),

                         Forms\Components\TextInput::make('operation_value')
                            ->label('Times base unit')
                            ->required( fn ($get) => !blank($get("operator")))
                            ->numeric(),
                            
                            Forms\Components\Select::make('base_unit')
                            ->label('Select base unit')
                            ->required( fn ($get) => !blank($get("operator")))
                            ->options(Productunit::whereNull('base_unit')->pluck('name','id'))
                            ->preload()
                            ->searchable(),
                        
                    ])
                    ->columns(3),

                
                
                ])
                ->columns(2),
            ]);
        }
        
        public static function table(Table $table): Table
        {
            return $table
            ->columns([
                Tables\Columns\TextColumn::make('name')
                ->state(fn ($record) => $record->base_unit ? ($record->name."(".$record->operation_value." ".$record->unit?->name."(s))") : $record->name)
                ->searchable(),
                Tables\Columns\TextColumn::make('code')
                ->label('Short name')
                ->searchable(),
                // Tables\Columns\TextColumn::make('base_unit')
                // ->searchable(),
                // Tables\Columns\TextColumn::make('operator')
                // ->searchable(),
                // Tables\Columns\TextColumn::make('operation_value')
                // ->numeric()
                // ->sortable(),
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
                            'index' => Pages\ListProductunits::route('/'),
                           // 'create' => Pages\CreateProductunit::route('/create'),
                            'edit' => Pages\EditProductunit::route('/{record}/edit'),
                        ];
                    }
                }
                