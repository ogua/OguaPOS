<?php

namespace App\Filament\Resources;

use App\Filament\Resources\FundTransferResource\Pages;
use App\Filament\Resources\FundTransferResource\RelationManagers;
use App\Models\FundTransfer;
use App\Models\PaymentAccount;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class FundTransferResource extends Resource
{
    protected static ?string $model = FundTransfer::class;

    protected static ?string $navigationIcon = 'heroicon-o-truck';
    protected static ?string $slug = 'fund-transfer';
    protected static ?string $navigationGroup = 'Payment Accounts';
    protected static ?string $navigationLabel = 'Fund Transfer';
    protected static ?string $modelLabel = 'Fund Transfer';
    protected static ?int $navigationSort = 2;

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Section::make('')
                    ->description('')
                    ->schema([
                        Forms\Components\Hidden::make('user_id')
                        ->default(auth()->user()->id),

                    Forms\Components\Select::make('transfer_from')
                        ->required()
                        ->options(PaymentAccount::pluck('account_name','id'))
                        ->preload()
                        ->searchable(),

                    Forms\Components\Select::make('transfer_to')
                        ->required()
                        ->options(PaymentAccount::pluck('account_name','id'))
                        ->preload()
                        ->searchable(),

                    Forms\Components\TextInput::make('amount')
                        ->required()
                        ->prefix(auth()->user()->pos?->currncy?->currency_code ?? 'GHC'),

                    Forms\Components\DateTimePicker::make('transfer_date')
                        ->required(),
                    Forms\Components\Textarea::make('note')
                        ->columnSpanFull(),
                    Forms\Components\FileUpload::make('attachment')
                    ->columnSpanFull(),
                    ])
                    ->columns(2),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('accfrom.account_name')
                    ->numeric()
                    ->sortable(),
                Tables\Columns\TextColumn::make('accto.account_name')
                    ->numeric()
                    ->sortable(),
                Tables\Columns\TextColumn::make('amount')
                    ->numeric()
                    ->sortable(),
                Tables\Columns\TextColumn::make('transfer_date')
                    ->dateTime()
                    ->sortable(),
                Tables\Columns\TextColumn::make('attachment')
                    ->searchable(),
                Tables\Columns\TextColumn::make('user.name')
                    ->label('Action By')
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
                Tables\Actions\DeleteAction::make()
                ->before(function ($record) {
                    $from = $record->transfer_from;
                    $to = $record->transfer_to;
                    $amount = $record->amount;

                    $fromacc = PaymentAccount::where('id',$from)->first();
                    $bal = $fromacc->current_balance;
                    $fromacc->current_balance = $bal + $amount;
                    $fromacc->save();

                    $toacc = PaymentAccount::where('id',$to)->first();
                    $bal = $toacc->current_balance;
                    $toacc->current_balance = $bal - $amount;
                    $toacc->save();
                }),
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
            'index' => Pages\ListFundTransfers::route('/'),
            'create' => Pages\CreateFundTransfer::route('/create'),
            'edit' => Pages\EditFundTransfer::route('/{record}/edit'),
            'account-books' => Pages\Accountbook::route('/{record}/account-book')
        ];
    }
}
