<?php

namespace App\Filament\Resources;

use App\Filament\Resources\SalesResource\Pages;
use App\Filament\Resources\SalesResource\RelationManagers;
use App\Livewire\Sound;
use App\Models\Clients;
use App\Models\Coupon;
use App\Models\Giftcard;
use App\Models\Product;
use App\Models\Product_Warehouse_Inventory;
use App\Models\Sales;
use App\Models\Stock_History;
use App\Models\Taxrates;
use App\Models\Warehouse;
use App\Services\Saleservice;
use App\Services\SalesForm;
use Filament\Forms;
use Filament\Forms\Components\Actions\Action;
use Filament\Forms\Form;
use Filament\Infolists\Components\TextEntry;
use Filament\Notifications\Notification;
use Filament\Resources\Resource;
use Filament\Support\Enums\MaxWidth;
use Filament\Tables;
use Filament\Tables\Actions\ActionGroup;
use Filament\Tables\Columns\Summarizers\Sum;
use Filament\Tables\Enums\ActionsPosition;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Builder;
use Icetalker\FilamentTableRepeater\Forms\Components\TableRepeater;
use Illuminate\Contracts\View\View;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class SalesResource extends Resource
{
    protected static ?string $model = Sales::class;
    
    protected static ?string $navigationIcon = 'heroicon-o-circle-stack';
    protected static ?string $slug = 'sales';
    protected static ?string $navigationGroup = 'Sale';
    protected static ?string $navigationLabel = 'Sale';
    protected static ?string $modelLabel = 'Sale';
    protected static ?int $navigationSort = 1;
    
    public static function getEloquentQuery(): Builder
    {
        return parent::getEloquentQuery()
        ->orderBy('id','desc');
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
                ->visible(fn ($operation) => $operation == 'create')
                ->schema(SalesForm::creatformschema()),

             Forms\Components\Section::make('')
                ->description('')
                ->visible(fn ($operation) => $operation == 'edit')
                ->schema(SalesForm::Editformschema()),
        ]);
    }
    
                                                
public static function table(Table $table): Table
{
return $table
->recordUrl("")
->recordClasses(fn (Sales $record) => $record->grand_total > 0 ? 'border-l-4 border-l-red-500 bg-red-50 dark:bg-red-800' : null)
->columns([
Tables\Columns\TextColumn::make('transaction_date')
->label('Date')
->date()
->sortable(),
Tables\Columns\TextColumn::make('sales_type')
->label('Sales Type')
->searchable(),
Tables\Columns\TextColumn::make('reference_number')
->label('Reference')
->searchable(),
Tables\Columns\TextColumn::make('user_id')
->hidden()
->searchable(),
Tables\Columns\TextColumn::make('customer.name')
->searchable(),
Tables\Columns\TextColumn::make('biller.name')
->label('Biller')
->searchable(),
Tables\Columns\TextColumn::make('sale_status')
->searchable(),

Tables\Columns\TextColumn::make('payment_status')
->searchable(),

Tables\Columns\TextColumn::make('item')
->numeric()
->sortable()
->toggleable(isToggledHiddenByDefault: true),
Tables\Columns\TextColumn::make('total_qty')
->numeric()
->sortable()
->toggleable(isToggledHiddenByDefault: true),

Tables\Columns\TextColumn::make('total_price')
->formatStateUsing(fn (string $state): string => "GHC ".number_format($state,2))
->sortable()
->toggleable(isToggledHiddenByDefault: true),

Tables\Columns\TextColumn::make('grand_total')
->formatStateUsing(fn (string $state): string => "GHC ".number_format($state,2))
->sortable(),
//->summarize(Sum::make()->money('GHC')->label('Total sales')),

Tables\Columns\TextColumn::make('paid_amount')
->formatStateUsing(fn (string $state): string => "GHC ".number_format($state,2))
->sortable()
->summarize(Sum::make()->money('GHC')),

Tables\Columns\TextColumn::make('currency_id')
->numeric()
->sortable()
->toggleable(isToggledHiddenByDefault: true),

Tables\Columns\TextColumn::make('order_tax_rate')
->searchable()
->toggleable(isToggledHiddenByDefault: true),
Tables\Columns\TextColumn::make('order_tax')
->searchable()
->toggleable(isToggledHiddenByDefault: true),
Tables\Columns\TextColumn::make('order_discount_type')
->searchable()
->toggleable(isToggledHiddenByDefault: true),
Tables\Columns\TextColumn::make('order_discount_value')
->numeric()
->sortable()
->toggleable(isToggledHiddenByDefault: true),
Tables\Columns\TextColumn::make('coupon_id')
->numeric()
->sortable()
->toggleable(isToggledHiddenByDefault: true),
Tables\Columns\TextColumn::make('coupon_discount')
->numeric()
->sortable()
->toggleable(isToggledHiddenByDefault: true),
Tables\Columns\TextColumn::make('shipping_cost')
->numeric()
->sortable()
->toggleable(isToggledHiddenByDefault: true),

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
    
    Tables\Actions\EditAction::make(),

    //Tables\Actions\ViewAction::make(),

    Tables\Actions\Action::make('view')
        ->label('View')
        ->icon('heroicon-m-eye')
        ->modalHeading(fn ($record) => $record->reference_number." Sales")
        ->modalWidth(MaxWidth::SixExtraLarge)
        ->modalSubmitAction(false)
        ->modalContent(fn (Sales $record): View => view(
            'filament.resources/sales-resource.pages.view-sale',
            ['record' => $record],
    )),

    Tables\Actions\Action::make("editshipping")
    ->label('Add / Edit Shipping')
    ->icon('heroicon-m-truck')
    ->url(fn (Sales $record): string => self::getUrl('delivery',['record' => $record])),

    Tables\Actions\Action::make('Print Invoice')
    ->icon('heroicon-m-receipt-percent')
    ->color('success')
    ->url( fn ($record) => route('pos-invoice', $record->id), shouldOpenInNewTab: true),


    // Tables\Actions\Action::make('packingslips')
    // ->label('Packing Slip 1')
    // ->icon('heroicon-m-inbox-stack')
    // ->modalWidth(MaxWidth::SixExtraLarge)
    // ->modalSubmitAction(false)
    // ->modalCancelAction(false)
    // ->modalContent(fn (Sales $record): View => view(
    //     'filament.resources.sales-resource.pages.packing-invoice',
    //     ['sale' => $record, 'pos' => $record->pos],
    // )),

    Tables\Actions\Action::make('packingslip')
     ->label('Packing Slip')
    ->icon('heroicon-m-inbox-stack')
    ->url( fn ($record) => route('sale-packing-slip', $record->id), shouldOpenInNewTab: true),


    Tables\Actions\Action::make('payments')
    ->label('View Payments')
    ->icon('heroicon-m-banknotes')
    ->modalWidth(MaxWidth::SixExtraLarge)
    ->modalHeading("")
    ->modalSubmitAction(false)
    ->modalContent(fn (Sales $record): View => view(
        'filament.resources.sales-resource.pages.view-sale-payment',
        ['record' => $record, 'recordtype' => 'Sales'],
    )),

    Tables\Actions\Action::make('updatepayments')
    ->label('Add Payment')
    ->icon('heroicon-m-banknotes')
    ->modalWidth(MaxWidth::ScreenMedium)
    ->modalSubmitAction(false)
    ->modalCancelAction(false)
    ->modalContent(fn (Sales $record): View => view(
        'filament.resources.sales-resource.pages.sale-update-payment',
        ['record' => $record,'recordtype' => 'Sales'],
    )),


    // Tables\Actions\DeleteAction::make()
    // ->before(function (Sales $record) {

    //     if ($record->gift_card_id) {
    //         $giftCard = Giftcard::find($record->gift_card_id);
    //         $giftCard->expense -= $record->paid_amount;
    //         $giftCard->save();
    //     }

    //     if ($record->coupon_id) {
    //         $coupon = Coupon::find($record->coupon_id);
    //         $coupon->used -= 1;
    //         $coupon->available += 1;
    //         $coupon->save();
    //     }

    //     foreach ($record->saleitem as $item) {
    //         $product = Product::find($item->product_id);
    //         $warehouseId = $record->warehouse_id;

    //         if ($product->product_type == "Single") {
    //             $inventory = Product_Warehouse_Inventory::where('product_id', $product->id)
    //                 ->where('warehouse_id', $warehouseId)
    //                 ->first();

    //             $inventory->qty += $item->qty;
    //             $inventory->save();

    //             Stock_History::where('adjustment_item_id', $record->id)
    //                 ->where('type', 'Sales')
    //                 ->delete();
    //         } elseif ($product->product_type == "Variation") {
    //             $inventory = Product_Warehouse_Inventory::where('product_id', $product->id)
    //                 ->where('warehouse_id', $warehouseId)
    //                 ->where('variant_id', $item->variant_id)
    //                 ->first();

    //             $inventory->qty += $item->qty;
    //             $inventory->save();

    //             Stock_History::where('adjustment_item_id', $record->id)
    //                 ->where('type', 'Sales')
    //                 ->delete();
    //         }
    //     }

    //     $record->saleitem()->delete();
    //     $record->payments()->delete();
    //     $record->delivery()->deliveryhistort()->delete();
    //     $record->delivery()->delete();
    // }),



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
        'index' => Pages\ListSales::route('/'),
        'create' => Pages\CreateSales::route('/create'),
        'edit' => Pages\EditSales::route('/{record}/edit'),
        'delivery' => Pages\Saledelivery::route('/{record}/shipping'),
        'payment-account-report' => Pages\PaymentAccountReport::route('/payment-account-report'),
    ];
}


}
                                                            