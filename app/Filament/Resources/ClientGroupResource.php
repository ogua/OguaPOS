<?php

namespace App\Filament\Resources;

use App\Filament\Resources\ClientGroupResource\Pages;
use App\Filament\Resources\ClientGroupResource\RelationManagers;
use App\Models\ClientGroup;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class ClientGroupResource extends Resource
{
    protected static ?string $model = ClientGroup::class;
    
    protected static ?string $navigationIcon = 'heroicon-o-users';
    protected static ?string $slug = 'settings/users';
    protected static ?string $navigationGroup = 'Settings';
    protected static ?string $navigationLabel = 'Customer group';
    protected static ?string $modelLabel = 'Customer group';
    protected static ?int $navigationSort = 2;
    
    public static function form(Form $form): Form
    {
        return $form
        ->schema([
            Forms\Components\Section::make('')
            ->description('')
            ->schema([
                Forms\Components\TextInput::make('name')
                ->required()
                ->maxLength(255),
                Forms\Components\TextInput::make('percentage')
                ->required()
                ->numeric(),
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
                Tables\Columns\TextColumn::make('name')
                ->searchable(),
                Tables\Columns\TextColumn::make('percentage')
                ->numeric()
                ->sortable(),
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
                            'index' => Pages\ListClientGroups::route('/'),
                            'create' => Pages\CreateClientGroup::route('/create'),
                            'edit' => Pages\EditClientGroup::route('/{record}/edit'),
                        ];
                    }
                }
                