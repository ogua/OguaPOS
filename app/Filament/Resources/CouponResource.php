<?php

namespace App\Filament\Resources;

use App\Filament\Resources\CouponResource\Pages;
use App\Filament\Resources\CouponResource\RelationManagers;
use App\Models\Coupon;
use Filament\Forms;
use Filament\Forms\Components\Actions\Action;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use Keygen\Keygen;

class CouponResource extends Resource
{
    protected static ?string $model = Coupon::class;
    
    protected static ?string $navigationIcon = 'heroicon-o-newspaper';
    protected static ?string $slug = 'sale/coupons';
    protected static ?string $navigationGroup = 'Sale';
    protected static ?string $navigationLabel = 'Coupon';
    protected static ?string $modelLabel = 'Coupon';
    protected static ?int $navigationSort = 2;
    
    public static function form(Form $form): Form
    {
        return $form
        ->schema([
            Forms\Components\Section::make('')
            ->description('')
            ->schema([
                Forms\Components\TextInput::make('code')
                ->required()
                ->hintAction(
                    Action::make("code")
                    ->icon('heroicon-m-arrow-path')
                    ->label("")
                    ->action(function(Forms\Set $set, Forms\Get $get){
                        $set("code",Keygen::alphanum(10)->generate());
                    })
                ),
                Forms\Components\Select::make('coupon_type')
                ->required()
                ->searchable()
                ->options([
                    'Flat' => 'Flat',
                    'Discount' => 'Discount'
                ]),
                Forms\Components\TextInput::make('amount')
                ->required()
                ->numeric(),
                Forms\Components\TextInput::make('qty')
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
                Tables\Columns\TextColumn::make('code')
                ->searchable(),
                Tables\Columns\TextColumn::make('coupon_type')
                ->searchable(),
                Tables\Columns\TextColumn::make('amount')
                ->numeric()
                ->sortable(),
                Tables\Columns\TextColumn::make('qty')
                ->numeric()
                ->sortable(),
                Tables\Columns\TextColumn::make('available')
                ->numeric()
                ->sortable(),
                Tables\Columns\TextColumn::make('expiry_date')
                ->date()
                ->sortable(),
                
                 Tables\Columns\IconColumn::make('is_active')
                ->boolean(),

                Tables\Columns\TextColumn::make('user.name')
                ->label('Added By')
                ->searchable(),

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
                            'index' => Pages\ListCoupons::route('/'),
                            'create' => Pages\CreateCoupon::route('/create'),
                            'edit' => Pages\EditCoupon::route('/{record}/edit'),
                        ];
                    }
                }
                