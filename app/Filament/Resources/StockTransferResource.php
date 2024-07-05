<?php

namespace App\Filament\Resources;

use App\Filament\Resources\StockTransferResource\Pages;
use App\Filament\Resources\StockTransferResource\RelationManagers;
use App\Models\Product;
use App\Models\StockTransfer;
use App\Models\Warehouse;
use App\Services\Saleservice;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Actions\Action;
use Filament\Tables\Actions\ActionGroup;
use Filament\Tables\Enums\ActionsPosition;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use Icetalker\FilamentTableRepeater\Forms\Components\TableRepeater;
use Illuminate\Database\Eloquent\Collection;
use Mockery\Undefined;

class StockTransferResource extends Resource
{
    protected static ?string $model = StockTransfer::class;
    
    protected static ?string $navigationIcon = 'heroicon-o-truck';
    protected static ?string $slug = 'stock-transfer';
    protected static ?string $navigationGroup = 'Stock Management';
    protected static ?string $navigationLabel = 'Stock Transfer';
    protected static ?string $modelLabel = 'Stock Transfer';
    protected static ?int $navigationSort = 2;
    
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
                Forms\Components\DateTimePicker::make('date')
                //->native(false)
                ->seconds(false)
                ->required(),
                Forms\Components\TextInput::make('reference_no')
                ->maxLength(255),
                Forms\Components\Select::make('status')
                ->options([
                    'Pending' => 'Pending',
                    'In Transit' => 'In Transit',
                    'Completed' => 'Completed'
                    ])
                    ->searchable()
                    ->helperText('Stock cant be edited if status is completed')
                    ->required(),
                    
                    Forms\Components\Select::make('from_warehouse_id')
                    ->label('Location (From)')
                    ->options(Warehouse::where('is_active',true)->pluck('name','id'))
                    ->preload()
                    ->searchable()
                    ->live()
                    ->required(),
                    
                    Forms\Components\Select::make('to_warehouse_id')
                    ->label('Location (To)')
                    ->options(Warehouse::where('is_active',true)->pluck('name','id'))
                    ->preload()
                    ->searchable()
                    ->live()
                    ->required(),
                    
                    Forms\Components\Select::make('scan_code')
                    ->label('')
                    ->allowHtml()
                    ->live()
                    ->hidden(fn (Forms\Get $get) => blank($get('from_warehouse_id')) || blank($get('to_warehouse_id')))
                    // ->options(Product::all()->pluck('product_name', 'id'))
                    ->getSearchResultsUsing(fn (string $search,Forms\Get $get): array => (new Saleservice())->getadjustmentproduct($search,$get("from_warehouse_id")))
                    ->preload()
                    ->placeholder("Search product by name / code")
                    ->columnSpanFull()
                    ->searchable()
                    ->dehydrated(false)
                    ->afterStateUpdated(function (Forms\Set $set,Forms\Get $get, ?string $state) {
                        $repeaterItems = $get('transferitems');
                        
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
                            'variant_id' => $state[1] ?? "",
                            'unit_price' => $px,
                            'qty' => 1,
                            'total' => number_format($px,2),
                        ];
                        
                        // If state doesn't exist, add it to the array
                        if (!$stateExists) {
                            array_push($repeaterItems, $data);
                        }
                        
                        $set('transferitems', $repeaterItems);
                        $set("scan_code","");
                        
                    }),
                    
                    TableRepeater::make('transferitems')
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
                        ->debounce(1000)
                        ->live()
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
                        
                        Forms\Components\TextInput::make('shipping_charge')
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
                    ->groups([
                        'date',
                        'status',
                    ])
                    ->defaultGroup('date')
                    ->groupRecordsTriggerAction(
                        fn (Action $action) => $action
                            ->button()
                            ->label('Group records'),
                    )
                    ->columns([
                        Tables\Columns\TextColumn::make('date')
                        ->date()
                        ->sortable(),
                        Tables\Columns\SelectColumn::make('status')
                        ->options([
                            'Pending' => 'Pending',
                            'In Transit' => 'In Transit',
                            'Completed' => 'Completed'
                            ])
                            ->rules(['required'])
                            ->afterStateUpdated(function ($record, $state) {
                                // update stock.

                                //update stock history
                            }),
                            Tables\Columns\TextColumn::make('reference_no')
                            ->searchable(),
                            Tables\Columns\TextColumn::make('fromwarehouse.name')
                            ->label("From Location")
                            ->sortable(),
                            Tables\Columns\TextColumn::make('towarehouse.name')
                            ->label("To Location")
                            ->sortable(),
                            
                            Tables\Columns\TextColumn::make('shipping_charge')
                            ->state(function (StockTransfer $record): string {
                                return "GHC ".$record->shipping_charge;
                            })
                            ->sortable(),
                            
                            Tables\Columns\TextColumn::make('total_amount')
                            ->label('Amount received')
                            ->state(function (StockTransfer $record): string {
                                return "GHC ".$record->transferitems()->sum('total');
                            })
                            ->sortable(),
                            
                            // Tables\Columns\TextColumn::make('note')
                            // ->label('Note')
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
                                    ActionGroup::make([
                                        Tables\Actions\ViewAction::make(),
                                        Tables\Actions\EditAction::make()
                                        ->visible(fn (StockTransfer $record) => $record->status != "Completed"),
                                        Tables\Actions\Action::make('Delete')
                                        ->icon('heroicon-m-trash')
                                        ->color('danger')
                                        ->requiresConfirmation()
                                        ->action(function (StockTransfer $record) {
                                            $record->transferitems()->delete();
                                            $record->delete();
                                        }),
                                        ])
                                        ->dropdownPlacement('top-start')
                                    ], position: ActionsPosition::BeforeCells)
                                    ->bulkActions([
                                        Tables\Actions\BulkActionGroup::make([
                                            Tables\Actions\BulkAction::make('delete')
                                            ->requiresConfirmation()
                                            ->action(function (Collection $records) {
                                                foreach($records as $record){                                        
                                                    $record->transferitems()->delete();
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
                                        'index' => Pages\ListStockTransfers::route('/'),
                                        'create' => Pages\CreateStockTransfer::route('/create'),
                                        'view' => Pages\ViewStockTransfer::route('/{record}'),
                                        'edit' => Pages\EditStockTransfer::route('/{record}/edit'),
                                    ];
                                }
                            }
                            