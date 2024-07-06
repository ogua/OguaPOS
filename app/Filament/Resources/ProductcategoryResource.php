<?php

namespace App\Filament\Resources;

use App\Filament\Resources\ProductcategoryResource\Pages;
use App\Filament\Resources\ProductcategoryResource\RelationManagers;
use App\Models\Productcategory;
use Filament\Facades\Filament;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class ProductcategoryResource extends Resource
{
    protected static ?string $model = Productcategory::class;

    protected static ?string $navigationIcon = 'heroicon-o-document-duplicate';

    protected static ?string $slug = 'products/create-category';
    protected static ?string $navigationGroup = 'Products';
    protected static ?string $navigationLabel = 'Category';
    protected static ?string $modelLabel = 'Category';
    protected static ?int $navigationSort = 1;


    protected static ?string $tenantOwnershipRelationshipName = 'owner';
   // $tenantRelationshipName


    // public static function getEloquentQuery(): Builder
    // {
    //     return parent::getEloquentQuery()->where('uniqueid', auth()->user()->uniqueid)
    //     ->orderBy('id','desc');
    // }
    

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Section::make('')
                    ->description('')
                    ->schema([
                        Forms\Components\TextInput::make('user_created_id')
                    ->default(auth()->user()->id)
                    ->dehydrated()
                    ->hidden(),

                    Forms\Components\TextInput::make('name')
                        ->required()
                        ->maxLength(255),
                        
                    Forms\Components\Textarea::make('description')
                        ->maxLength(65535)
                        ->columnSpanFull(),

                    // Forms\Components\Hidden::make('warehouse_id')
                    //     ->default(Filament::getTenant()->id)



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
                // Tables\Columns\TextColumn::make('total')
                //     ->state(fn ($record) => $record->products->inventory->sum('qty')),
                
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
              'index' => Pages\ListProductcategories::route('/'),
            //'create' => Pages\CreateProductcategory::route('/create'),
            //'edit' => Pages\EditProductcategory::route('/{record}/edit'),
        ];
    }
}
