<?php

namespace App\Filament\Resources;

use App\Filament\Resources\StockCountResource\Pages;
use App\Filament\Resources\StockCountResource\RelationManagers;
use App\Models\Productcategory;
use App\Models\Stockcount;
use App\Models\Warehouse;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class StockCountResource extends Resource
{
    protected static ?string $model = Stockcount::class;

    protected static ?string $navigationIcon = 'heroicon-o-shopping-cart';
    protected static ?string $slug = 'products/stock-count';
    protected static ?string $navigationGroup = 'Products';
    protected static ?string $navigationLabel = 'Stock Count';
    protected static ?string $modelLabel = 'Stock Count';
    protected static ?int $navigationSort = 6;

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
                        Forms\Components\Select::make('warehouse_id')
                            ->label('Warehouse')
                            ->options(Warehouse::pluck('name','id'))
                            ->preload()
                            ->searchable()
                            ->required()
                            ->visibleOn('create'),

                        Forms\Components\Select::make('count_type')
                            ->options([
                                'Full' => 'Full',
                                'Partial' => 'Partial'
                            ])
                            ->required()
                            ->afterStateUpdated(function ($state,$set){
                                if ($state == "Full") {
                                    $set("category_id","");
                                    $set("brand_id","");
                                }
                            })
                            ->live()
                            ->searchable()
                            ->visibleOn('create'),

                        Forms\Components\Select::make('category_id')
                            ->label('Category')
                            ->options(Productcategory::pluck('name','id'))
                            ->preload()
                            ->searchable()
                            ->visible(fn ($get) => $get("count_type") == "Partial"),

                        Forms\Components\Select::make('brand_id')
                            ->label('Brand')
                            ->options(Productcategory::pluck('name','id'))
                            ->preload()
                            ->searchable()
                            ->visible(fn ($get) => $get("count_type") == "Partial"),

                        
                            Forms\Components\FileUpload::make('final_file')
                            ->label('Final file')
                            ->columnSpanFull()
                            ->visibleOn('edit')
                            ->helperText('You just need to update the Counted column in the initial file'),
                       
                    ])
                    ->columns(2),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
        ->recordAction("")
            ->columns([
                Tables\Columns\TextColumn::make('created_at')
                ->label('Date')
                    ->dateTime()
                    ->sortable(),
                Tables\Columns\TextColumn::make('warehouse.name')
                    ->searchable(),
                Tables\Columns\TextColumn::make('category.name')
                    ->numeric()
                    ->sortable(),
                Tables\Columns\TextColumn::make('brand.name')
                    ->numeric()
                    ->sortable(),
                Tables\Columns\TextColumn::make('count_type')
                ->label('Type')
                ->badge()
                ->color(fn ($state) => $state == "Full" ? 'info' : 'success')
                ->searchable(),

                Tables\Columns\TextColumn::make('intital_file')
                ->icon('heroicon-o-arrow-down-tray')
               // ->state(fn ($record) => $record->final_file ? "download" : 'hhh')
                ->url(fn (StockCount $record): string => asset('storage')."/stock_count/".$record->intital_file),

                Tables\Columns\TextColumn::make('final_file')
                ->icon('heroicon-o-arrow-down-tray')
                //->state(fn () => "download")
                ->url(fn (StockCount $record): string => $record->final_file ? asset('storage')."/stock_count/".$record->final_file : '#'),


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
                ->label('Finalize')
                ->icon("")
                ->visible(fn ($record) => blank($record->final_file)),

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
            'index' => Pages\ListStockCounts::route('/'),
            'create' => Pages\CreateStockCount::route('/create'),
            //'edit' => Pages\EditStockCount::route('/{record}/edit'),
        ];
    }
}
