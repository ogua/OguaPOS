<?php

namespace App\Filament\Cashier\Resources;

use Filament\Forms;
use Filament\Tables;
use App\Models\Product;
use Filament\Forms\Form;
use App\Models\Delivery;
use Filament\Tables\Table;
use Filament\Resources\Resource;
use Filament\Support\Enums\MaxWidth;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use App\Filament\Cashier\Resources\DeliveriesResource\Pages;
use App\Filament\Cashier\Resources\DeliveriesResource\RelationManagers;
use Filament\Tables\Actions\ActionGroup;
use Filament\Tables\Enums\ActionsPosition;
use Icetalker\FilamentTableRepeater\Forms\Components\TableRepeater;

class DeliveriesResource extends Resource
{
    protected static ?string $model = Delivery::class;

    protected static ?string $navigationIcon = 'heroicon-o-truck';

    protected static ?int $navigationSort = 4;
    protected static ?string $slug = 'shipments';
    protected static ?string $navigationGroup = 'Sale';
    protected static ?string $navigationLabel = 'Shipments';
    protected static ?string $modelLabel = 'Shipment';

    public static function getEloquentQuery(): Builder
    {
        return parent::getEloquentQuery()
        ->orderBy('id','desc');
    }

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\TextInput::make('order_id')
                    ->required()
                    ->numeric(),
                Forms\Components\DatePicker::make('expected')
                    ->required(),
                Forms\Components\DatePicker::make('deliveredon')
                    ->required(),
                Forms\Components\Select::make('deliveredon')
                ->label('Status')
                ->options([
                    'Delivered' => 'Delivered',
                    'Cancelled' => 'Cancelled'
                ])
                ->required(),
                    
            ]);
    }

    // `sale_id`, `shipping_detail`, `shipping_address`, `shipping_status`,
    //  `delivered_to`, `shipping_note`, `expected`,
    //   `deliveredon`, `shipping_documents`,

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('order.reference_number')
                ->label('Order Ref')
                    ->numeric()
                    ->searchable(),
                Tables\Columns\TextColumn::make('order.customer.name')
                ->label('Customer Name')
                    ->numeric()
                    ->searchable(),
                Tables\Columns\TextColumn::make('order.customer.phone_number')
                ->label('Customer Contact')
                    ->numeric()
                    ->sortable(),
                Tables\Columns\TextColumn::make('total_items')
                ->numeric()
                ->badge()
                ->state(fn ($record) => $record->order->saleitem?->sum('qty') ?? 0)
                ->sortable(),
                
                Tables\Columns\TextColumn::make('grand_total')
                ->numeric()
                ->sortable()
                ->state(fn ($record) => "GHC ".($record->order?->grand_total ?? 0))
                ->badge(),
                Tables\Columns\TextColumn::make('expected')
                ->label('Expected Delivery')
                ->date()
                ->sortable(),

                Tables\Columns\TextColumn::make('deliveredon')
                ->label('Deliverd On')
                    ->date()
                    ->sortable(),

                Tables\Columns\TextColumn::make('delivered_to')
                ->label('Received By')
                ->searchable(),

                Tables\Columns\TextColumn::make('shipping_status')
                ->label('Delivery status')
                ->badge()
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

                 ActionGroup::make([
                //Tables\Actions\ViewAction::make(),
                //Tables\Actions\EditAction::make(),
                Tables\Actions\Action::make("Download")
                ->icon('heroicon-m-arrow-down-tray')
                ->color('info')
                ->url(fn (Delivery $record): string => route('delivered-order', $record))
                ->openUrlInNewTab(),

                Tables\Actions\Action::make('View products')
                ->icon('heroicon-m-document-duplicate')
                ->color('success')
                ->modalHeading(fn ($record) => ucwords(strtolower($record->order->reference_number))." Product Information")
                ->modalSubmitAction(false)
                ->modalWidth(MaxWidth::SevenExtraLarge)
                ->fillForm(fn (Delivery $record): array => [
                    'orderitems' => $record->order?->saleitem
                ])
                ->form([
                Forms\Components\Section::make('')
                    ->description('')
                    ->schema([

                        Forms\Components\Group::make()
                        ->schema([

                        TableRepeater::make('orderitems')
                                ->label('')
                                    //->relationship()
                                    ->columnSpan("full")
                                    ->schema([

                                        Forms\Components\Hidden::make('product_id'),
                                        Forms\Components\Hidden::make('variant_id'),
                                        Forms\Components\Hidden::make('sale_unit_id'),
                                        Forms\Components\Hidden::make('stock')
                                        ->dehydrated(false),
                                        
                                        Forms\Components\TextInput::make('product_name')
                                        ->columnSpan(2),
                                        
                                        Forms\Components\TextInput::make('unit_price')
                                        ->type("number")
                                        ->readOnly()
                                        ->label('Price')
                                        ->required(),
                                        
                                        
                                        
                                        Forms\Components\TextInput::make('qty')
                                        ->integer()
                                        ->default(1)
                                        ->readOnly()
                                        ->live(onBlur: true)
                                        ->afterStateUpdated(function (Forms\Get $get,Forms\Set $set, $state,$livewire){
                                            $tot = $state * $get('unit_price');
                                            
                                            $pxqty = $get("stock");
                                            
                                            if ($state > $pxqty) {
                                                $livewire->dispatch('play-error');
                                                $set("qty",$state - 1);
                                                static::calculateitems($set,$get);
                                                return;
                                                
                                            }elseif($state < 0){
                                                $livewire->dispatch('play-error');
                                                $set("qty",0);
                                                static::calculateitems($set,$get);
                                                return;
                                            }
                                            
                                            
                                            $set("tax",(($get("tax_rate") / 100) * $tot));
                                            $set("total",($tot + ($get("tax_rate") / 100) * $tot));                            
                                            //self::updateTotals($get,$set);
                                        })
                                        ->afterStateHydrated(function (Forms\Get $get,Forms\Set $set, $state){
                                            $tot = $state * $get('unit_price');
                                            
                                            
                                            $set("tax",(($get("tax_rate") / 100) * $tot));
                                            $set("total",($tot + ($get("tax_rate") / 100) * $tot));                            
                                            //self::updateTotals($get,$set);
                                        })
                                        ->required(),
                                        
                                        
                                        Forms\Components\Hidden::make('discount'),
                                        Forms\Components\TextInput::make('tax_rate')
                                        ->readOnly(),
                                        Forms\Components\TextInput::make('tax')
                                        ->readOnly(),
                                        
                                        
                                        Forms\Components\Placeholder::make('ptotal')
                                        ->content(function ($get,$set){
                                            $tot = $get("qty") * $get('unit_price');
                                            
                                            $set("total",($tot + ($get("tax_rate") / 100) * $tot));  
                                            return ($tot + ($get("tax_rate") / 100) * $tot);
                                        })
                                        ->label('Total'),
                                        
                                        Forms\Components\Hidden::make('total')
                                        ->default(0),
                                        
                                        ])
                                        ->addActionLabel('Add Item')
                                        ->addable(false)
                                        ->deletable(false)
                                        ->mutateRelationshipDataBeforeCreateUsing(function (array $data): array {
                                           // $data['amount'] = auth()->user()->uniqueid;
                                            return $data;
                                        }),
                        ])
                        
                    ]),
                
                ])
                ->action(function (array $data, Delivery $record): void {   
                    
                    
                    
                }),

                ])
            ], position: ActionsPosition::BeforeCells)

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
            'index' => Pages\ListDeliveries::route('/'),
            'create' => Pages\CreateDeliveries::route('/create'),
            'view' => Pages\ViewDeliveries::route('/{record}'),
            'edit' => Pages\EditDeliveries::route('/{record}/edit'),
        ];
    }
}
