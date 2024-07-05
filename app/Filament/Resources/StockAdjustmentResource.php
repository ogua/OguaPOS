<?php

namespace App\Filament\Resources;

use App\Filament\Resources\StockAdjustmentResource\Pages;
use App\Filament\Resources\StockAdjustmentResource\RelationManagers;
use App\Models\Product;
use App\Models\Product_Warehouse_Inventory;
use App\Models\Stock_History;
use App\Models\StockAdjustment;
use App\Models\Warehouse;
use App\Services\Saleservice;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Enums\ActionsPosition;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use Icetalker\FilamentTableRepeater\Forms\Components\TableRepeater;
use Illuminate\Database\Eloquent\Collection;

class StockAdjustmentResource extends Resource
{
    protected static ?string $model = StockAdjustment::class;
    
    protected static ?string $navigationIcon = 'heroicon-o-rectangle-stack';
    protected static ?string $slug = 'stock-adjusment';
    protected static ?string $navigationGroup = 'Stock Management';
    protected static ?string $navigationLabel = 'Stock Adjustment';
    protected static ?string $modelLabel = 'Stock Adjustment';
    protected static ?int $navigationSort = 1;
    
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
                ->required()
                ->live(),
                
                Forms\Components\TextInput::make('reference_no')
                ->maxLength(255),
                
                Forms\Components\DateTimePicker::make('date')
                ->required(),
                
                Forms\Components\Select::make('adjustment_type')
                ->helperText("Normal: Like leakages, damages. Abnormal: like fire, accident")
                ->options(['Normal' => 'Normal', 'Abnormal' => 'Abnormal'])
                ->searchable()
                ->columnSpan(2)
                ->required(),
                
                Forms\Components\Select::make('scan_code')
                ->label('')
                ->allowHtml()
                ->visible(fn ($get) => !blank($get('warehouse_id')))
                // ->options(Product::all()->pluck('product_name', 'id'))
                ->getSearchResultsUsing(fn (string $search,Forms\Get $get): array => (new Saleservice())->getadjustmentproduct($search,$get("warehouse_id")))
                ->preload()
                ->placeholder("Search product by name / code")
                ->columnSpanFull()
                ->searchable()
                ->dehydrated(false)
                ->live()
                ->afterStateUpdated(function (Forms\Set $set,Forms\Get $get, ?string $state) {
                    $repeaterItems = $get('stockitems');
                    
                    $state = explode(",",$state);
                    
                    // Flag to check if state exists
                    $stateExists = false;
                    
                    $product = Product::find($state[0]);
                    
                    $product_type = $product->product_type;
                    
                    // Loop through the items array
                    foreach ($repeaterItems as $key => $item) {
                        if ($product_type == "Single" && $item['product_id'] === $state[0]) {
                            // State exists, update quantity and total price
                            $repeaterItems[$key]['qty'] += 1;
                            $repeaterItems[$key]['total'] = $repeaterItems[$key]['qty'] * $item['unit_price'];
                            $stateExists = true;
                            break; // Exit the loop since the state has been found
                        }elseif ($product_type == "Variation" && $item['product_id'] === $state[0] && $item['variant_id'] === $state[1]) {
                            $repeaterItems[$key]['qty'] += 1;
                            $repeaterItems[$key]['total'] = number_format($repeaterItems[$key]['qty'] * $item['unit_price'],2);
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
                        'variant_id' => $state[1] ?? null,
                        'unit_price' => $px,
                        'qty' => 1,
                        'total' => number_format($px,2),
                    ];
                    
                    // If state doesn't exist, add it to the array
                    if (!$stateExists) {
                        array_push($repeaterItems, $data);
                    }
                    
                    $set('stockitems', $repeaterItems);
                    $set("scan_code","");
                    
                }),
                
                TableRepeater::make('stockitems')
                ->label('')
                ->relationship()
                ->live()
                ->schema([
                    
                    Forms\Components\TextInput::make('product_name')
                    ->label('Product name')
                    ->readOnly(),
                    
                    Forms\Components\Hidden::make('product_id'),
                    Forms\Components\Hidden::make('variant_id'),
                    
                    
                    Forms\Components\TextInput::make('unit_price')
                    ->readOnly(),
                    
                    Forms\Components\TextInput::make('qty')
                    ->numeric()
                    ->live()
                    ->debounce(500)
                    ->afterStateUpdated(function (Forms\Get $get,Forms\Set $set, $state){
                        $tot = $state * $get('unit_price');
                        $set("total",$tot);
                    }),
                    
                    Forms\Components\TextInput::make('total')
                    ->readOnly()
                    ->label('Total')
                    
                    ])
                    ->defaultItems(0)
                    ->columnSpanFull()
                    ->addable(false)
                    ->addActionLabel('Add'),
                    
                    Forms\Components\TextInput::make('total_amount_recovered')
                    ->required()
                    ->numeric()
                    ->default(0.00),
                    
                    Forms\Components\Textarea::make('note')
                    ->maxLength(65535)
                    ->columnSpan(2),
                    ])
                    ->columns(3),
                ]);
            }
            
            public static function table(Table $table): Table
            {
                return $table
                ->columns([
                    Tables\Columns\TextColumn::make('date')
                    ->dateTime()
                    ->sortable(),
                    
                    Tables\Columns\TextColumn::make('reference_no')
                    ->searchable(),
                    
                    Tables\Columns\TextColumn::make('warehouse.name')
                    ->label('Location')
                    ->numeric()
                    ->sortable(),
                    
                    Tables\Columns\TextColumn::make('adjustment_type')
                    ->label('Type')
                    ->searchable(),
                    
                    Tables\Columns\TextColumn::make('total_amount')
                    ->label('Total amount')
                    ->state(function (StockAdjustment $record): string {
                        return "GHC ".$record->stockitems()->sum('total');
                    })
                    ->sortable(),
                    
                    Tables\Columns\TextColumn::make('total_amount_recovered')
                    ->label('Amount received')
                    ->state(function (StockAdjustment $record): string {
                        return "GHC ".$record->total_amount_recovered;
                    })
                    ->sortable(),
                    
                    Tables\Columns\TextColumn::make('note')
                    ->label('Reason')
                    ->sortable(),
                    
                    Tables\Columns\TextColumn::make('user.name')
                    ->label('Added By')
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
                            Tables\Actions\ViewAction::make(),
                            //Tables\Actions\EditAction::make(),
                            // Tables\Actions\DeleteAction::make(),
                            Tables\Actions\Action::make('Delete')
                            ->icon('heroicon-m-trash')
                            ->requiresConfirmation()
                            ->action(function (StockAdjustment $record) {
                                
                                foreach ($record->stockitems as $stock) {
                                    $producttype = $stock->product->product_type;
                                    $productid = $stock->product_id;
                                    $variantid = $stock->variant_id;
                                    $qty = $stock->qty;
                                    
                                    if ($producttype == "Single") {
                                        
                                        $currentstock = Product_Warehouse_Inventory::where('product_id',$productid)
                                        ->first();
                                        
                                        $currentstock->qty+=$qty;
                                        $currentstock->save();
                                        
                                    }elseif ($producttype == "Variation") {
                                        
                                        $currentstock = Product_Warehouse_Inventory::where('product_id',$productid)
                                        ->where('variant_id',$variantid)
                                        ->first();
                                        
                                        $currentstock->qty+=$qty;
                                        $currentstock->save();
                                    }
                                    
                                    //update stock history
                                    $stockhistory = [
                                        'product_id' => $productid,
                                        'warehouse_id' => $record->warehouse_id,
                                        'variant_id' => $variantid,
                                        'adjustment_item_id' => $stock->id,
                                        'type' => 'Stock Adjustment',
                                        'qty_change' => "+".$qty,
                                        'new_quantity' => $currentstock->qty,
                                        'date' => now(),
                                        'reference' => date('Ymdhms'),
                                    ];
                                    
                                    Stock_History::create($stockhistory);
                                }
                                
                                
                                $record->stockitems()->delete();
                                $record->delete();
                            }),
                        ], position: ActionsPosition::BeforeCells)
                        ->bulkActions([
                            Tables\Actions\BulkActionGroup::make([
                                Tables\Actions\DeleteBulkAction::make(),
                                Tables\Actions\BulkAction::make('delete')
                                ->requiresConfirmation()
                                ->action(function (Collection $records) {
                                    foreach($records as $record){
                                        
                                        foreach ($record->stockitems as $stock) {
                                            $producttype = $stock->product->product_type;
                                            $productid = $stock->product_id;
                                            $variantid = $stock->variant_id;
                                            $qty = $stock->qty;
                                            
                                            if ($producttype == "Single") {
                                                
                                                $currentstock = Product_Warehouse_Inventory::where('product_id',$productid)
                                                ->first();
                                                
                                                $currentstock->qty+=$qty;
                                                $currentstock->save();
                                                
                                            }elseif ($producttype == "Variation") {
                                                
                                                $currentstock = Product_Warehouse_Inventory::where('product_id',$productid)
                                                ->where('variant_id',$variantid)
                                                ->first();
                                                
                                                $currentstock->qty+=$qty;
                                                $currentstock->save();
                                            }
                                            
                                            //update stock history
                                            $stockhistory = [
                                                'product_id' => $productid,
                                                'warehouse_id' => $record->warehouse_id,
                                                'variant_id' => $variantid,
                                                'adjustment_item_id' => $stock->id,
                                                'type' => 'Stock Adjustment',
                                                'qty_change' => "+".$qty,
                                                'new_quantity' => $currentstock->qty,
                                                'date' => now(),
                                                'reference' => date('Ymdhms'),
                                            ];
                                            
                                            Stock_History::create($stockhistory);
                                        }
                                        
                                        $record->stockitems()->delete();
                                        $record->delete();
                                    }
                                })->deselectRecordsAfterCompletion(),
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
                            'index' => Pages\ListStockAdjustments::route('/'),
                            'create' => Pages\CreateStockAdjustment::route('/create'),
                            'view' => Pages\ViewStockAdjustment::route('/{record}'),
                            'edit' => Pages\EditStockAdjustment::route('/{record}/edit'),
                        ];
                    }
                }
                