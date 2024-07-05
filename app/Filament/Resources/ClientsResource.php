<?php

namespace App\Filament\Resources;

use App\Filament\Resources\ClientsResource\Pages;
use App\Filament\Resources\ClientsResource\RelationManagers;
use App\Models\ClientGroup;
use App\Models\Clients;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class ClientsResource extends Resource
{
    protected static ?string $model = Clients::class;
    
    protected static ?string $navigationIcon = 'heroicon-o-user';
    protected static ?string $slug = 'people/client';
    protected static ?string $navigationGroup = 'People';
    protected static ?string $navigationLabel = 'Customer';
    protected static ?string $modelLabel = 'Customer';
    protected static ?int $navigationSort = 2;
    
    public static function form(Form $form): Form
    {
        return $form
        ->schema([
            Forms\Components\Section::make('')
            ->description('')
            ->schema([
                Forms\Components\Select::make('client_group_id')
                ->label('Customer group')
                ->required()
                ->options(ClientGroup::where('is_active',true)->pluck('name','id'))
                ->preload()
                ->searchable(),
                Forms\Components\Hidden::make('user_id')
                ->default(auth()->user()->id),
                Forms\Components\TextInput::make('name')
                ->required()
                ->maxLength(255),
                Forms\Components\TextInput::make('company_name')
                ->maxLength(255),
                Forms\Components\TextInput::make('email')
                ->email()
                ->maxLength(255),
                Forms\Components\TextInput::make('phone_number')
                ->tel()
                ->required()
                ->maxLength(255),
                Forms\Components\TextInput::make('tax_no')
                ->maxLength(255),
                Forms\Components\Textarea::make('address')
                ->required()
                ->maxLength(65535)
                ->columnSpanFull(),
                Forms\Components\TextInput::make('city')
                ->required()
                ->maxLength(255),
                Forms\Components\TextInput::make('state')
                ->maxLength(255),
                Forms\Components\TextInput::make('postal_code')
                ->maxLength(255),
                Forms\Components\TextInput::make('country')
                ->maxLength(255),
                Forms\Components\TextInput::make('points')
                ->required()
                ->maxLength(255)
                ->default(0),
                Forms\Components\TextInput::make('deposit')
                ->required()
                ->maxLength(255)
                ->default(0),
                Forms\Components\TextInput::make('expense')
                ->required()
                ->maxLength(255)
                ->default(0),
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
                Tables\Columns\TextColumn::make('group.name')
                ->label('Customer group')
                ->searchable(),
                // Tables\Columns\TextColumn::make('user_id')
                // ->searchable(),
                Tables\Columns\TextColumn::make('name')
                ->searchable(),
                Tables\Columns\TextColumn::make('company_name')
                ->searchable(),
                Tables\Columns\TextColumn::make('email')
                ->searchable(),
                Tables\Columns\TextColumn::make('phone_number')
                ->searchable(),
                Tables\Columns\TextColumn::make('tax_no')
                ->searchable(),
                Tables\Columns\TextColumn::make('address')
                ->searchable(),
                Tables\Columns\TextColumn::make('city')
                ->searchable(),
                Tables\Columns\TextColumn::make('state')
                ->searchable(),
                Tables\Columns\TextColumn::make('postal_code')
                ->searchable(),
                Tables\Columns\TextColumn::make('country')
                ->searchable(),
                Tables\Columns\TextColumn::make('points')
                ->searchable(),
                Tables\Columns\TextColumn::make('deposit')
                ->searchable(),
                Tables\Columns\TextColumn::make('expense')
                ->searchable(),
                Tables\Columns\IconColumn::make('is_active')
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
                        Tables\Actions\EditAction::make(),
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
                            'index' => Pages\ListClients::route('/'),
                            'create' => Pages\CreateClients::route('/create'),
                            'edit' => Pages\EditClients::route('/{record}/edit'),
                        ];
                    }
                }
                