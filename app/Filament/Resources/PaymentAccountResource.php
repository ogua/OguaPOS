<?php

namespace App\Filament\Resources;

use App\Filament\Resources\PaymentAccountResource\Pages;
use App\Filament\Resources\PaymentAccountResource\RelationManagers;
use App\Models\PaymentAccount;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class PaymentAccountResource extends Resource
{
    protected static ?string $model = PaymentAccount::class;

    protected static ?string $navigationIcon = 'heroicon-o-banknotes';
    protected static ?string $slug = 'payment-account';
    protected static ?string $navigationGroup = 'Payment Accounts';
    protected static ?string $navigationLabel = 'Accounts';
    protected static ?string $modelLabel = 'Accounts';
    protected static ?int $navigationSort = 1;

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Section::make('')
                    ->description('')
                    ->schema([
                    Forms\Components\Hidden::make('user_id')
                        ->default(auth()->user()->id),
                    Forms\Components\TextInput::make('account_name')
                        ->required()
                        ->maxLength(255),
                    Forms\Components\TextInput::make('account_number')
                        ->required()
                        ->maxLength(255),
                    Forms\Components\Select::make('account_type')
                        ->options([
                            'WAREHOUSE ACCOUNT' => 'WAREHOUSE ACCOUNT',
                            'CURRENT ACCOUNT' => 'CURRENT ACCOUNT',
                            'CUSTOMER ACCOUNT' => 'CUSTOMER ACCOUNT',
                        ])
                        ->searchable()
                        ->required(),
                    Forms\Components\TextInput::make('opening_balance')
                        ->prefix(auth()->user()->pos?->currncy?->currency_code ?? 'GHC')
                        ->required()
                        ->numeric(),
                        
                    Forms\Components\Hidden::make('current_balance')
                    ->default(0),
                        
                    Forms\Components\KeyValue::make('account_details')
                        ->addActionLabel('Add detail')
                        ->keyLabel('Label')
                        ->valueLabel('Value')
                        ->columnSpanFull(),
                    Forms\Components\Textarea::make('note')
                        ->columnSpanFull(),
                    Forms\Components\Toggle::make('status')
                        ->required(),
                    ])
                    ->columns(2),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('account_name')
                    ->searchable(),
                Tables\Columns\TextColumn::make('account_number')
                    ->searchable(),
                Tables\Columns\TextColumn::make('account_type')
                    ->searchable(),
                Tables\Columns\TextColumn::make('current_balance')
                    ->numeric()
                    ->sortable(),
                Tables\Columns\TextColumn::make('account_details'),
                Tables\Columns\IconColumn::make('status')
                    ->boolean(),
                Tables\Columns\TextColumn::make('user.name')
                ->label('Added By')
                    ->numeric()
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
                Tables\Actions\Action::make("accountbook")
                ->label('Account Book')
                ->color('success')
                ->icon('heroicon-m-banknotes')
                ->url(fn (PaymentAccount $record): string => PaymentResource::getUrl('payment-account-book',['record' => $record])),
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
            'index' => Pages\ListPaymentAccounts::route('/'),
            'create' => Pages\CreatePaymentAccount::route('/create'),
            'edit' => Pages\EditPaymentAccount::route('/{record}/edit'),
        ];
    }
}
