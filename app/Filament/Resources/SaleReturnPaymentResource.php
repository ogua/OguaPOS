<?php

namespace App\Filament\Resources;

use App\Filament\Resources\SaleReturnPaymentResource\Pages;
use App\Filament\Resources\SaleReturnPaymentResource\RelationManagers;
use App\Models\PaymentAccount;
use App\Models\SaleReturn;
use App\Models\SaleReturnPayment;
use App\Models\Sales;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class SaleReturnPaymentResource extends Resource
{
    protected static ?string $model = SaleReturnPayment::class;

    protected static ?string $navigationIcon = 'heroicon-o-rectangle-stack';

    protected static ?string $modelLabel = 'Payment';

    protected static bool $shouldRegisterNavigation = false;

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Hidden::make('user_id')
                    ->default(auth()->user()->id),
                Forms\Components\Hidden::make('sale_return_id'),
                Forms\Components\Select::make('sale_id')
                ->label('Return Ref')
                    ->options(SaleReturn::latest()->pluck('reference_no','id'))
                    ->preload()
                    ->searchable()
                    ->required()
                    ->live()
                    ->afterStateUpdated(fn ($state,$set) => $set("sale_return_id",$state)),
                Forms\Components\Select::make('account_id')
                    ->options(PaymentAccount::latest()->pluck('account_name','id'))
                    ->preload()
                    ->searchable()
                    ->required(),
                Forms\Components\DateTimePicker::make('paid_on'),
                Forms\Components\Select::make('paying_method')
                        ->options([
                            'CASH' => 'CASH',
                            'PAYPAL' => 'PAYPAL',
                            'CHEQUE' => 'CHEQUE',
                            'GIFT CARD' => 'GIFT CARD',
                            'CREDIT CARD' => 'CREDIT CARD',
                            'DRAFT' => 'DRAFT',
                            'BANK TRANSFER' => 'BANK TRANSFER'
                            ])
                            ->required(),
                Forms\Components\TextInput::make('payment_amount')
                    ->required()
                    ->numeric()
                    ->prefix("GHC"),
                Forms\Components\FileUpload::make('attach_document'),
                Forms\Components\Textarea::make('payment_note')
                    ->columnSpanFull(),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->recordUrl("")
            ->columns([
                Tables\Columns\TextColumn::make('return.reference_no')
                    ->label('Reference')
                    ->numeric()
                    ->sortable(),
                Tables\Columns\TextColumn::make('account.account_name')
                    ->numeric()
                    ->sortable(),
                Tables\Columns\TextColumn::make('paid_on')
                    ->dateTime()
                    ->sortable(),
                Tables\Columns\TextColumn::make('paying_method')
                    ->searchable(),
                Tables\Columns\TextColumn::make('payment_amount')
                    ->label('Amount')
                    ->sortable()
                    ->state(fn (SaleReturnPayment $record) => 'GHC '.$record->payment_amount)
                    ->badge(),
                Tables\Columns\TextColumn::make('attach_document')
                    ->searchable(),
                Tables\Columns\TextColumn::make('user.name')
                    ->label('Action by')
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
            'index' => Pages\ListSaleReturnPayments::route('/'),
            'create' => Pages\CreateSaleReturnPayment::route('/create'),
            'edit' => Pages\EditSaleReturnPayment::route('/{record}/edit'),
        ];
    }
}
