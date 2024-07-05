<?php

namespace App\Filament\Resources;

use App\Filament\Resources\ProductPromotionResource\Pages;
use App\Filament\Resources\ProductPromotionResource\RelationManagers;
use App\Models\Product;
use App\Models\ProductPromotion;
use App\Models\Warehouse;
use App\Services\Saleservice;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Actions\Action;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use Icetalker\FilamentTableRepeater\Forms\Components\TableRepeater;

class ProductPromotionResource extends Resource
{
    protected static ?string $model = ProductPromotion::class;
    
    protected static ?string $navigationIcon = 'heroicon-o-clipboard-document-list';
    protected static ?string $slug = 'products/promotion';
    protected static ?string $navigationGroup = 'Products';
    protected static ?string $navigationLabel = 'Product Promo';
    protected static ?string $modelLabel = 'Promotion';
    protected static ?int $navigationSort = 3;
    
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
                ->options(Warehouse::where('is_active',true)->pluck('name','id'))
                ->preload()
                ->searchable()
                ->live()
                ->required(),
                
                
                Forms\Components\Select::make('scan_code')
                ->label('')
                ->live()
                ->visible(fn ($operation) => $operation == 'create')
                ->hidden(fn (Forms\Get $get) => blank($get('warehouse_id')))
                // ->options(Product::all()->pluck('product_name', 'id'))
                ->getSearchResultsUsing(fn (string $search,Forms\Get $get): array => (new Saleservice())->getadjustmentproduct($search,$get("warehouse_id")))
                ->preload()
                ->placeholder("Search product by name / code")
                ->columnSpanFull()
                ->searchable()
                ->dehydrated(false)
                ->afterStateUpdated(function (Forms\Set $set,Forms\Get $get, ?string $state) {
                    $repeaterItems = $get('promotionitems');
                    
                    $state = explode(",",$state);
                    
                    // Flag to check if state exists
                    $stateExists = false;
                    
                    $product = Product::find($state[0]);
                    
                    $product_type = $product->product_type;
                    
                    // Loop through the items array
                    foreach ($repeaterItems as $key => $item) {
                        if ($product_type == "Single" && $item['product_id'] === $state[0]) {
                            $stateExists = true;
                            break; // Exit the loop since the state has been found
                        }elseif ($product_type == "Variation" && $item['product_id'] === $state[0] && $item['variant_id'] === $state[1]) {
                            $stateExists = true;
                            break; // Exit the loop since the state has been found
                        }
                    }
                    
                    
                    if ($product_type == "Single") {
                        $name = $product->product_name;
                        $px = $product->getinventorypx()['costpx'];
                        
                    }elseif ($product_type == "Variation") {
                        $product_variant_data = $product->variationitems()
                        ->where('id',$state[1])->first();
                        $code = $product_variant_data->item_code;
                        $name = $product_variant_data->product->product_name." ".$code;
                        $px = $product_variant_data->cost_price;
                    }
                    
                    $data = [
                        'product_name' =>  $name,
                        'product_id' => $state[0],
                        'variant_id' => $state[1] ?? "",
                        'promotion_price' => 0,
                        'start_date' => "",
                        'end_date' => "",
                        'status' => false,
                    ];
                    
                    // If state doesn't exist, add it to the array
                    if (!$stateExists) {
                        array_push($repeaterItems, $data);
                    }
                    
                    $set('promotionitems', $repeaterItems);
                    $set("scan_code","");
                    
                }),
                
                TableRepeater::make('promotionitems')
                ->label('')
                ->live()
                ->visible(fn ($operation) => $operation == 'create')
                ->schema([
                    
                    Forms\Components\TextInput::make('product_name')
                    ->label('Product name')
                    ->readOnly(),
                    
                    Forms\Components\Hidden::make('product_id'),
                    Forms\Components\Hidden::make('variant_id'),
                    
                    
                    Forms\Components\TextInput::make('promotion_price')
                    ->numeric(),
                    
                    Forms\Components\DatePicker::make('start_date')
                    ->native(false)
                    ->required(),
                    
                    Forms\Components\DatePicker::make('end_date')
                    ->native(false)
                    ->required(),
                    
                    Forms\Components\Toggle::make('status')
                    ->required(),
                    
                    
                    ])
                    ->defaultItems(0)
                    ->columnSpanFull()
                    ->addable(false)
                    ->addActionLabel('Add'),


                Forms\Components\TextInput::make('product_name')
                    ->label('Product name')
                    ->visible(fn ($operation) => $operation == 'edit')
                    ->readOnly(),                    
                    
                    Forms\Components\TextInput::make('promotion_price')
                    ->visible(fn ($operation) => $operation == 'edit')
                    ->numeric(),
                    
                    Forms\Components\DatePicker::make('start_date')
                    ->visible(fn ($operation) => $operation == 'edit')
                    ->native(false)
                    ->required(),
                    
                    Forms\Components\DatePicker::make('end_date')
                    ->visible(fn ($operation) => $operation == 'edit')
                    ->native(false)
                    ->required(),
                    
                    Forms\Components\Toggle::make('status')
                    ->visible(fn ($operation) => $operation == 'edit')
                    ->required(),






                    
                    ])
                    ->columns(2),
                ]);
            }
            
            public static function table(Table $table): Table
            {
                return $table
                ->groups([
                    'start_date',
                    'end_date',
                    'status',
                    ])
                    ->defaultGroup('start_date')
                    ->columns([
                        Tables\Columns\TextColumn::make('warehouse.name')
                        ->numeric()
                        ->sortable(),
                        Tables\Columns\TextColumn::make('product_name')
                        ->searchable(),
                        // Tables\Columns\TextColumn::make('product_id')
                        //     ->numeric()
                        //     ->sortable(),
                        // Tables\Columns\TextColumn::make('variant_id')
                        //     ->numeric()
                        //     ->sortable(),
                       
                        Tables\Columns\TextColumn::make('promotion_price')
                        ->state(function (ProductPromotion $record): string {
                            return "GHC ".$record->promotion_price;
                        })
                        ->badge()
                        ->sortable(),
                        Tables\Columns\TextColumn::make('start_date')
                        ->date()
                        ->sortable(),
                        Tables\Columns\TextColumn::make('end_date')
                        ->date()
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
                                    'index' => Pages\ListProductPromotions::route('/'),
                                    'create' => Pages\CreateProductPromotion::route('/create'),
                                    'edit' => Pages\EditProductPromotion::route('/{record}/edit'),
                                ];
                            }
                        }
                        