<?php

namespace App\Filament\Resources;

use App\Filament\Resources\VariationResource\Pages;
use App\Filament\Resources\VariationResource\RelationManagers;
use App\Models\Variation;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class VariationResource extends Resource
{
    protected static ?string $model = Variation::class;

    protected static ?string $navigationIcon = 'heroicon-o-swatch';

    protected static ?string $slug = 'products/variation';
    protected static ?string $navigationGroup = 'Products';
    protected static ?string $navigationLabel = 'Variation';
    protected static ?string $modelLabel = 'Variation';
    protected static ?int $navigationSort = 4;

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Section::make('')
                    ->description('')
                    ->schema([
                        Forms\Components\TextInput::make('name')
                        ->columnSpanFull()
                        ->placeholder("Color, size, Packaging")
                        ->required()
                        ->maxLength(255),

                        Forms\Components\Repeater::make('variationvalues')
                        ->label('')
                        ->relationship()
                        ->schema([
                            
                            Forms\Components\TextInput::make('value')
                            ->required()
                            ->placeholder("small,medium,large")
                            ->maxLength(255),

                        ])
                        ->columnSpanFull()
                        ->grid(3)
                        ->addActionLabel('Add value')
                    ])
                    ->columns(2),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('name')
                    ->searchable(),

                Tables\Columns\TextColumn::make('variationvalues.value')
                ->label('Value')
                    ->searchable(),

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
            'index' => Pages\ListVariations::route('/'),
            'create' => Pages\CreateVariation::route('/create'),
            'edit' => Pages\EditVariation::route('/{record}/edit'),
        ];
    }
}
