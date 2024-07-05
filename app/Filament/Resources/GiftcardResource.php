<?php

namespace App\Filament\Resources;

use App\Filament\Resources\GiftcardResource\Pages;
use App\Filament\Resources\GiftcardResource\RelationManagers;
use App\Models\Giftcard;
use App\Models\GiftcardRecharge;
use Filament\Forms;
use Filament\Forms\Components\Actions\Action;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use Keygen\Keygen;

class GiftcardResource extends Resource
{
    protected static ?string $model = Giftcard::class;
    
    protected static ?string $navigationIcon = 'heroicon-o-credit-card';
    protected static ?string $slug = 'sale/giftcard';
    protected static ?string $navigationGroup = 'Sale';
    protected static ?string $navigationLabel = 'Gift card';
    protected static ?string $modelLabel = 'Gift card';
    protected static ?int $navigationSort = 3;
    
    public static function form(Form $form): Form
    {
        return $form
        ->schema([
            Forms\Components\Section::make('')
            ->description('')
            ->schema([
                Forms\Components\TextInput::make('card_no')
                ->required()
                ->hintAction(
                    Action::make("code")
                    ->icon('heroicon-m-arrow-path')
                    ->label("")
                    ->action(function(Forms\Set $set, Forms\Get $get){
                        $set("card_no",Keygen::numeric(16)->generate());
                    })
                ),
                Forms\Components\TextInput::make('amount')
                ->required()
                ->numeric(),
                Forms\Components\DatePicker::make('expiry_date')
                ->required(),
                Forms\Components\Hidden::make('user_id')
                ->default(auth()->user()->id),
                Forms\Components\Toggle::make('is_active')
                ->required(),
                ])
                ->columns(2),
            ]);
        }
        
        public static function table(Table $table): Table
        {
            return $table
            ->columns([
                Tables\Columns\TextColumn::make('card_no')
                ->searchable(),
                Tables\Columns\TextColumn::make('amount')
                ->formatStateUsing(fn (string $state): string => 'GHC'.number_format($state,2))
                ->sortable(),
                Tables\Columns\TextColumn::make('expense')
                ->formatStateUsing(fn (string $state): string => 'GHC'.number_format($state,2))
                ->sortable(),
                Tables\Columns\TextColumn::make('balance')
                ->state(fn (Giftcard $record): string => 'GHC'.($record->amount - $record->expense))
                ->color('warning')
                ->sortable(),
                Tables\Columns\TextColumn::make('user.name')
                ->label('Created By')
                ->searchable(),
                Tables\Columns\TextColumn::make('expiry_date')
                ->date()
                ->sortable()
                ->color(function (Giftcard $record) {
                    if ($record->expiry_date < date('Y-m-d')) {
                        return 'danger';
                    }else{
                        return 'success';
                    }
                }),
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
                        
                        Tables\Actions\Action::make('Recharge')
                        ->icon('heroicon-m-credit-card')
                        ->color('success')
                            ->form([

                                Forms\Components\TextInput::make('amount')
                                ->required()
                                ->numeric(),
                                
                                ])
                                ->action(function (array $data, Giftcard $record): void {  
                                    
                                    $amount = $record->amount;

                                    $add = (int) $amount + $data['amount'];

                                    $record->amount = $add;
                                    $record->save();

                                    $redata = [
                                        'user_id' => auth()->user()->id,
                                        'amount' => $data['amount'],
                                        'gift_card_id' => $record->id,
                                    ];

                                    GiftcardRecharge::create($redata);
                                                
                                }),

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
                            'index' => Pages\ListGiftcards::route('/'),
                            'create' => Pages\CreateGiftcard::route('/create'),
                            'edit' => Pages\EditGiftcard::route('/{record}/edit'),
                        ];
                    }
                }
                