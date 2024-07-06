<?php

namespace App\Filament\Cashier\Resources;

use App\Filament\Cashier\Resources\ProductResource\Pages;
use App\Filament\Cashier\Resources\ProductResource\RelationManagers;
use App\Filament\Resources\ProductResource\Widgets\ProductStatistics;
use App\Models\Brand;
use App\Models\Product;
use App\Models\Productcategory;
use App\Models\Productunit;
use App\Models\Taxrates;
use App\Models\Variablevalue;
use App\Models\Variation;
use App\Models\Warehouse;
use Filament\Tables\Actions\ActionGroup;
use Filament\Forms;
use Filament\Forms\Components\Actions\Action;
use Filament\Forms\Form;
use Filament\Forms\Get;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use Keygen\Keygen;
use Icetalker\FilamentTableRepeater\Forms\Components\TableRepeater;
use Filament\Infolists;
use Filament\Infolists\Components\Section;
use Filament\Infolists\Infolist;
use Filament\Resources\Pages\Page;
use Filament\Pages\SubNavigationPosition;
use Filament\Support\Enums\ActionSize;
use Filament\Support\Enums\MaxWidth;
use Filament\Tables\Columns\Summarizers\Summarizer;
use Filament\Tables\Enums\ActionsPosition;
use Illuminate\Contracts\View\View;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\HtmlString;
use Laravel\SerializableClosure\Serializers\Native;
use Illuminate\Support\Str;

class ProductResource extends Resource
{
   protected static ?string $model = Product::class;
    
    protected static ?string $navigationIcon = 'heroicon-o-device-tablet';
    
    protected static ?string $slug = 'products/new-product';
    protected static ?string $navigationGroup = 'Products';
    protected static ?string $navigationLabel = 'Products list';
    protected static ?string $modelLabel = 'Product';
    protected static ?int $navigationSort = 2;

    protected static ?string $tenantOwnershipRelationshipName = 'tenantwarehouse';
    //protected static ?string $tenantelationshipName = 'tenantwarehouse';

   // protected static ?string $cluster = ProductsCluster::class;

    //protected static SubNavigationPosition $subNavigationPosition = SubNavigationPosition::Start;

    protected static SubNavigationPosition $subNavigationPosition = SubNavigationPosition::Top;


    public static function getEloquentQuery(): Builder
    {
        return parent::getEloquentQuery()
        ->orderBy('product_name','asc');
    }


    public static function getNavigationBadge(): ?string
    {
        return static::getModel()::count();
    }
    
    public static function form(Form $form): Form
    {
        return $form
        ->schema([
            Forms\Components\Section::make('')
            ->description('')
            ->schema([
                Forms\Components\FileUpload::make('product_image')
                ->image()
                ->columnSpanFull(),
                
                Forms\Components\Select::make('warehouses')
                ->label('Warehouses / Shop')
                ->relationship()
                ->multiple()
                ->required()
                ->options(Warehouse::where('is_active',true)->pluck('name','id'))
                ->preload()
                ->searchable(),
                
                Forms\Components\TextInput::make('product_name')
                ->required()
                ->maxLength(255),
                
                Forms\Components\TextInput::make('product_code')
                ->required()
                ->hintAction(
                    Action::make("code")
                    ->icon('heroicon-m-arrow-path')
                    ->label("")
                    ->action(function(Forms\Set $set, Forms\Get $get){
                        $set("product_code",Keygen::numeric(8)->generate());
                    })
                ),
                Forms\Components\Select::make('barcode_symbology')
                ->options([
                    'C128' => 'Code 128',
                    'C39' => 'Code 39',
                    'UPCA' => 'UPC-A',
                    'UPCE' => 'UPC-E',
                    'EAN8' => 'EAN-8',
                    'EAN13' => 'EAN-13',
                    ])
                    ->default('Code 128')
                    ->searchable(),
                    
                    Forms\Components\Select::make('brand_id')
                    ->label('Brand')
                    ->options(Brand::pluck('name','id'))
                    ->preload()
                    ->searchable(),
                    Forms\Components\Select::make('product_category_id')
                    ->label('Product Category')
                    ->required()
                    ->options(Productcategory::pluck('name','id'))
                    ->preload()
                    ->searchable(),

                    Forms\Components\Select::make('product_unit_id')
                    ->label('Product unit')
                    ->required()
                    ->options(Productunit::whereNull('base_unit')->pluck('name','id'))
                    ->preload()
                    ->searchable()
                    ->live(),
                    // ->afterStateUpdated(function ($state, Forms\Set $set, Forms\Get $get){
                    //     $set("sale_unit_id", $state);
                    //     $set("purchase_unit_id",$state);
                    // }),
                    
                    Forms\Components\Select::make('sale_unit_id')
                    ->label('Sales unit')
                    ->options(function($get){
                        return Productunit::where("base_unit", $get('product_unit_id'))
                        ->orWhere('id',$get('product_unit_id'))->pluck('name','id');
                    })
                    ->preload()
                    ->searchable()
                    ->required(),
                    

                    Forms\Components\Select::make('purchase_unit_id')
                    ->label('Purchase unit')
                    ->options(function($get){
                        return Productunit::where("base_unit", $get('product_unit_id'))
                        ->orWhere('id',$get('product_unit_id'))->pluck('name','id');
                    })
                    ->preload()
                    ->searchable()
                    ->required(),

                    Forms\Components\Hidden::make('daily_sales_objectives'),
                    Forms\Components\TextInput::make('alert_quantity')
                    ->numeric(),
                    Forms\Components\Select::make('taxes')
                    ->relationship()
                    ->options(Taxrates::pluck('name','id'))
                    ->multiple()
                    ->searchable()
                    ->preload(),
                    Forms\Components\Select::make('tax_method')
                    ->helperText("Exclusive: Product = Actual Product price + Tax, Inclusive: Actual Product price = Product - Tax")
                    ->options([
                        1 =>  'Tax Exclusive',
                        2 => 'Tax Inclusive'
                        ])
                        ->default(1),
                        
                        Forms\Components\DatePicker::make('product_expiry_date'),
                        Forms\Components\TextInput::make('product_batch_number')
                        ->maxLength(255),
                        Forms\Components\Textarea::make('product_details')
                        ->maxLength(65535)
                        ->columnSpanFull(),
                        
                        Forms\Components\Toggle::make('promotional_price')
                        ->label('Add Promotional Price')
                        ->live(),
                        
                        Forms\Components\Section::make('Promotional Price')
                        ->description('Add Promotional Price')
                        ->relationship('promotion')
                        ->schema([
                            Forms\Components\TextInput::make('promotion_price'),
                            
                            Forms\Components\DatePicker::make('promotion_start'),
                            
                            Forms\Components\DatePicker::make('promotion_end'),
                            ])
                            ->visible(fn (Get $get): bool => $get('promotional_price'))
                            ->columns(2),
                            
                            Forms\Components\Select::make('product_type')
                            ->options([
                                'Single' => 'Single Product',
                                'Variation' => 'Variation Product',
                                'Combo' => 'Combo Product'
                                ])
                                ->required()
                                ->searchable()
                                ->live(),
                                
                                // Forms\Components\Hidden::make('product_warehouse_id'),
                                Forms\Components\Hidden::make('user_created_id')
                                ->default(auth()->user()->id),
                                // Forms\Components\Toggle::make('active')
                                // ->required(),
                                ])
                                ->columns(3),
                                
                                Forms\Components\Section::make('Product Variation Details')
                                ->description('')
                                ->visible(fn (Get $get): bool => $get('product_type') == 'Variation')
                                ->schema([
                                    Forms\Components\Select::make('variationtype')
                                    ->label('Variation Type')
                                    ->options(Variation::pluck('name','id'))
                                    ->preload()
                                    ->searchable()
                                    ->columnSpan(2)
                                    ->dehydrated(false)
                                    ->live()
                                    ->afterStateUpdated(function (Forms\Set $set,Forms\Get $get, ?string $state){
                                        $variationitems = $get('variationitems');
                                        
                                        $myarray = [];
                                        
                                        $values = Variation::where('id',$state)->first();
                                        
                                        if(!$values){
                                            return;
                                        }
                                        
                                        if (count(collect($variationitems)) > 0) {
                                            
                                            foreach ($variationitems as $items) {
                                                
                                                foreach ($values->variationvalues as $var) {
                                                    
                                                    $combination = $items['item_code'].'/'.$var->value;
                                                    $vids = $items['variants_id'].'/'.$var->id;
                                                    
                                                    if (!in_array($combination, $myarray)) {
                                                        $mdata = [
                                                            'variants_id' => $vids,
                                                            'item_code' => $items['code'].'/'.$var->value."-".$get('product_code'),
                                                            'item_name' => $items['code'].'/'.$var->value,
                                                            'code' => $var->value,
                                                            'position' => $items['position'],
                                                            'cost_price' => $items['cost_price'],
                                                            'selling_price' => $items['selling_price'],
                                                        ];
                                                        
                                                        array_push($myarray,$mdata);
                                                    }
                                                }
                                            }
                                            
                                            $set('variationitems', $myarray);
                                            $set("variationtype","");
                                            
                                        }else{
                                            
                                            foreach ($values->variationvalues as $key => $var) {
                                                
                                                $data = [
                                                    'variants_id' => $var->id,
                                                    'item_code' => $var->value."-".$get('product_code'),
                                                    'item_name' => $var->value,
                                                    'code' => $var->value,
                                                    'cost_price' => 0,
                                                    'position' => $key + 1,
                                                    'selling_price' => 0,
                                                ];
                                                
                                                array_push($variationitems, $data);
                                            }
                                            
                                            $set('variationitems', $variationitems);
                                            $set("variationtype","");
                                            
                                        }
                                        
                                    }),
                                    
                                    TableRepeater::make('variationitems')
                                    ->label('')
                                    ->relationship()
                                    ->live()
                                    ->schema([
                                        Forms\Components\FileUpload::make('product_image')
                                        ->label('image'),
                                        Forms\Components\Hidden::make('item_code'),
                                        Forms\Components\TextInput::make('item_name')
                                        ->label('name')
                                        ->readOnly(),
                                        Forms\Components\Hidden::make('variants_id'),
                                        Forms\Components\Hidden::make('position'),
                                        Forms\Components\TextInput::make('cost_price')
                                        ->label('Cost px'),
                                        Forms\Components\TextInput::make('selling_price')
                                        ->label('Selling px')
                                        ])
                                        ->defaultItems(0)
                                        ->columnSpanFull()
                                        ->addable(false)
                                        ->addActionLabel('Add'),
                                        
                                    ]),
                                    
                                    Forms\Components\Section::make('')
                                    ->description('')
                                    ->visible(fn (Get $get, $operation): bool => $get('product_type') == 'Single' && $operation == 'create')
                                    ->schema([
                                        Forms\Components\TextInput::make('product_cost')
                                        ->required()
                                        ->maxLength(255),
                                        Forms\Components\TextInput::make('product_price')
                                        ->required()
                                        ->maxLength(255),
                                        ])
                                        ->columns(2),


                                    
                                    Forms\Components\Section::make('')
                                    ->description('')
                                    ->visible(fn (Get $get, $operation): bool => $get('product_type') == 'Single' && $operation == 'edit')
                                    ->schema([

                                    TableRepeater::make('inventory')
                                    ->label('')
                                    ->relationship()
                                    ->live()
                                    ->schema([

                                            Forms\Components\TextInput::make('warehouse_id')
                                            ->label('Warehouse')
                                            ->disabled()
                                            ->afterStateHydrated(function ($state,$set){
                                                $warehouse = Warehouse::find($state);
                                                $set("warehouse_id",$warehouse->name);
                                            }),

                                            Forms\Components\TextInput::make('cost_price')
                                            ->required()
                                            ->maxLength(255),

                                            Forms\Components\TextInput::make('selling_price')
                                            ->required()
                                            ->maxLength(255),
                                        ])
                                        ->defaultItems(0)
                                        ->columnSpanFull()
                                        ->addable(false),
                                        
                                        ])
                                        ->columns(2),






                                    ]);
                                }
                                
                                public static function table(Table $table): Table
                                {
                                    return $table
                                    ->recordUrl("")
                                    ->columns([
                                        Tables\Columns\ImageColumn::make('product_image')
                                        ->defaultImageUrl('/images/no-image.jpeg')
                                        ->circular(),

                                        Tables\Columns\TextColumn::make('product_name')
                                        ->label('Product')
                                        ->searchable(),

                                        Tables\Columns\TextColumn::make('product_type')
                                        ->searchable()
                                        ->badge()
                                        ->color(fn ($record) => $record->product_type == "Single" ? 'info' : 'success'),

                                        Tables\Columns\TextColumn::make('warehouses.name')
                                        ->label('Warehouses')
                                        ->sortable()
                                        ->listWithLineBreaks()
                                        ->bulleted(),

                                         Tables\Columns\TextColumn::make('current_stock')
                                        ->state(fn ($record) => $record->inventory()->sum('qty')."(".$record->unit->code.")s")
                                        ->sortable(),

                                        Tables\Columns\TextColumn::make('purchase_price')
                                        ->label('Unit purchase price')
                                        ->state(function (Product $record): string {
                                            return "GHC ".$record->getinventorypx()['costpx'];
                                        })
                                        ->sortable(),

                                        Tables\Columns\TextColumn::make('selling_price')
                                        ->label('Unit selling price')
                                        ->state(function (Product $record): string {
                                            
                                            if ($record->product_type == "Single") {
                                                return "GHC ".$record->getinventorypx()['sellpx'];
                                            }
                                            return "GHC ".$record->getFirstVariantPrice(). " - GHC ".$record->getLastVariantPrice();
                                        })
                                        ->sortable(),

                                        Tables\Columns\TextColumn::make('product_code')
                                        ->label('SKU')
                                        ->searchable()
                                        ->copyable()
                                        ->copyMessage('SKU code copied')
                                        ->copyMessageDuration(1500),
                                    

                                        Tables\Columns\TextColumn::make('category.name')
                                        ->numeric()
                                        ->sortable(),

                                        Tables\Columns\TextColumn::make('brand.name')
                                        ->numeric()
                                        ->sortable(),

                                        Tables\Columns\TextColumn::make('taxes.name')
                                        ->listWithLineBreaks()
                                        ->bulleted(),

                                        Tables\Columns\TextColumn::make('barcode_symbology')
                                        ->searchable()
                                        ->hidden(),

                                        Tables\Columns\TextColumn::make('product_unit_id')
                                        ->numeric()
                                        ->sortable()
                                        ->hidden(),

                                        Tables\Columns\TextColumn::make('sale_unit_id')
                                        ->numeric()
                                        ->sortable()
                                        ->hidden(),

                                        Tables\Columns\TextColumn::make('purchase_unit_id')
                                        ->numeric()
                                        ->sortable()
                                        ->hidden(),
                                        Tables\Columns\TextColumn::make('daily_sales_objectives')
                                        ->searchable()
                                        ->hidden(),
                                        Tables\Columns\TextColumn::make('alert_quantity')
                                        ->searchable()
                                        ->hidden(),
                                        Tables\Columns\TextColumn::make('tax_method')
                                        ->numeric()
                                        ->sortable()
                                        ->hidden(),
                                        Tables\Columns\TextColumn::make('product_expiry_date')
                                        ->date()
                                        ->sortable()
                                        ->hidden(),
                                        Tables\Columns\TextColumn::make('product_batch_number')
                                        ->searchable()
                                        ->hidden(),
                                        Tables\Columns\IconColumn::make('promotional_price')
                                        ->boolean()
                                        ->hidden(),
                                        Tables\Columns\TextColumn::make('user_created_id')
                                        ->numeric()
                                        ->sortable()
                                        ->hidden(),

                                        Tables\Columns\IconColumn::make('active')
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
                                                ActionGroup::make([
                                                    //Tables\Actions\ViewAction::make(),
                                                    Tables\Actions\Action::make('view')
                                                    ->label('View')
                                                    ->icon('heroicon-m-eye')
                                                    ->modalHeading(fn ($record) => ucwords(strtolower($record->product_name))." Product Information")
                                                    ->modalWidth(MaxWidth::SixExtraLarge)
                                                    ->modalSubmitAction(false)
                                                    ->modalContent(fn (Product $record): View => view(
                                                        'product-info-modal',
                                                        ['record' => $record],
                                                    )),

                                                    Tables\Actions\Action::make("AddStock")
                                                    ->label('Add / Edit Opening Stock')
                                                    ->icon('heroicon-m-plus-circle')
                                                    ->hidden(fn ($record) => $record->inventory()->sum('qty') > 0)
                                                    ->url(fn (Product $record): string => self::getUrl('manage-stock',['record' => $record])),
                                                    
                                                    Tables\Actions\Action::make("history")
                                                    ->label('Product Stock History')
                                                    ->icon('heroicon-m-clipboard-document-check')
                                                    ->url(fn (Product $record): string => self::getUrl('product-history',['record' => $record])),

                                                     Tables\Actions\Action::make('pricegroup')
                                                    ->label('Add / Edit Price Group')
                                                    ->icon('heroicon-m-clipboard-document-check')
                                                    ->modalHeading(fn ($record) => $record->product_name." Price Group Edit / Add")
                                                    ->modalWidth(MaxWidth::SixExtraLarge)
                                                    ->modalSubmitAction(false)
                                                    ->modalCancelAction(false)
                                                    ->modalContent(fn (Product $record): View => view(
                                                        'price-group',
                                                        ['record' => $record],
                                                    )),

                                                    Tables\Actions\ReplicateAction::make()
                                                    ->label('Duplicate Product')
                                                    ->icon('heroicon-m-document-duplicate')
                                                    ->before(function () {
                                                        // Runs before the record has been replicated.
                                                    })
                                                    ->beforeReplicaSaved(function (Model $replica): void {
                                                        // Runs after the record has been replicated but before it is saved to the database.
                                                    })
                                                    ->after(function (Model $replica, Product $record): void {

                                                        if ($record->product_image) {
                                                            $originalImagePath = $record->product_image;
                                                            $newImageName = Str::random(10) . '_' . $record->product_image;
                                                            $newImagePath = $newImageName;

                                                                Storage::disk('public')->copy($originalImagePath, $newImagePath);
                                                                $replica->product_image = $newImageName;
                                                                $replica->save();
                                                            
                                                        }

                                                        //replicate warehouses
                                                        $relatedModels = $record->warehouses()->get();
                                                        $replica->warehouses()->sync($relatedModels->pluck('id')->toArray());

                                                        //replicate tax
                                                        $relatedModels = $record->taxes()->get();
                                                        $replica->taxes()->sync($relatedModels->pluck('id')->toArray());

                                                        //replicate inventory
                                                        $record->inventory->each(function ($relatedModel) use ($replica) {
                                                            $newRelatedModel = $relatedModel->replicate();
                                                            $newRelatedModel->product_id = $replica->id;
                                                            $newRelatedModel->qty = 0;
                                                            $newRelatedModel->wholesale_price = 0;
                                                            $newRelatedModel->save();
                                                        });

                                                        $record->variationitems->each(function ($relatedModel) use ($replica) {
                                                            $newRelatedModel = $relatedModel->replicate();
                                                            $newRelatedModel->product_id = $replica->id;
                                                            $newRelatedModel->save();
                                                        });
                                                        
                                                    })
                                                    ->successRedirectUrl(fn (Model $replica): string => url(self::getUrl("edit",['record' => $replica]))),

                                                    // Tables\Actions\DeleteAction::make()
                                                    // ->requiresConfirmation()
                                                    // ->before(function (Product $record){
                                                    //     Storage::delete("public/".$record->product_image);
                                                    //     $record->warehouses()->detach();
                                                    //     $record->variationitems()->delete();
                                                    //     $record->taxes()->detach();
                                                    //     $record->inventory()->delete();
                                                    //     $record->promotions()->delete();
                                                    //     $record->history()->delete();
                                                    // }),
                                                    
                                                    
                                                
                                                    
                                                    ])                                                    
                                                ], position: ActionsPosition::BeforeCells)
                                                    ->bulkActions([
                                                        Tables\Actions\BulkActionGroup::make([
                                                            Tables\Actions\DeleteBulkAction::make(),
                                                        ]),
                                                    ]);
                                                }
                                                
                                                
    public static function infolist(Infolist $infolist): Infolist
    {
        return $infolist
        ->schema([
            
            Infolists\Components\ViewEntry::make('status')
            ->view('product-info')
            ->columnSpanFull(),                                                                    
            
        ]);
    }
    
    public static function getWidgets(): array
    {
        return [
            ProductStatistics::class
        ];
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
            'index' => Pages\ListProducts::route('/'),
            'create' => Pages\CreateProduct::route('/create'),
            //'view' => Pages\ViewProduct::route('/{record}'),
            'edit' => Pages\EditProduct::route('/{record}/edit'),
            'manage-stock' => Pages\ManageStock::route('/{record}/manage'),
            'product-history' => Pages\ProductHistory::route('/{record}/history'),
        ];
    }

    public static function getRecordSubNavigation(Page $page): array
    {
        return $page->generateNavigationItems([
                Pages\ListProducts::class,
                Pages\CreateProduct::class
        ]);
    }


     /** @return Builder<Post> */
    public static function getGlobalSearchEloquentQuery(): Builder
    {
        return parent::getGlobalSearchEloquentQuery()->with(['warehouses', 'category', 'brand', 'warehouse', 'warehousepx', 'inventory', 'unit', 'promotions']);
    }

    public static function getGloballySearchableAttributes(): array
    {
        return ['product_name', 'product_code', 'barcode_symbology','warehouses.name', 'brand.name', 'category.name', 'unit.name'];
    }

    public static function getGlobalSearchResultDetails(Model $record): array
    {
        /** @var Post $record */
        $details = [];

        $details['product name'] = $record->product_name;

        if ($record->product_image) {
            $url = asset('storage')."/".$record->product_image;
            $details['imgae'] = new HtmlString('<img src="'.$url.'" class="w-12 h-12 object-cover rounded-t-xl m-auto" style="margin-bottom: 10px;" />');
        }

        // if ($record->warehouses) {
        //     $details['warehouse'] = $record->warehouses->name;
        // }

        if ($record->brand) {
            $details['Brand'] = $record->brand->name;
        }

        if ($record->category) {
            $details['Category'] = $record->category->name;
        }

        if ($record->product_type == "Single") {
            $details['Unit Selling price'] = "GHC ".$record->getinventorypx()['sellpx'];
        }

        if ($record->product_type == "Variation") {
            $details['Unit Selling price range'] = "GHC ".$record->getFirstVariantPrice(). " - GHC ".$record->getLastVariantPrice();
        }

        $details['Stock'] = $record->inventory()->sum('qty')."(".$record->unit->code.")s";


        return $details;
    }

    public static function getGlobalSearchResultActions(Model $record): array
    {
        return [
            Action::make('edit')
                ->url(static::getUrl('edit', ['record' => $record])),
        ];
    }




                                                 
}
