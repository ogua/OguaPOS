<?php

namespace App\Filament\Resources;

use App\Filament\Resources\SupplierResource\Pages;
use App\Filament\Resources\SupplierResource\RelationManagers;
use App\Models\Supplier;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class SupplierResource extends Resource
{
    protected static ?string $model = Supplier::class;
    
    protected static ?string $navigationIcon = 'heroicon-o-users';
    protected static ?string $slug = 'settings/suppliers';
    protected static ?string $navigationGroup = 'People';
    protected static ?string $navigationLabel = 'Suppliers';
    protected static ?string $modelLabel = 'Supplier';
    protected static ?int $navigationSort = 3;
    
    public static function form(Form $form): Form
    {
        return $form
        ->schema([
            Forms\Components\Section::make('')
            ->description('')
            ->schema([
                Forms\Components\Select::make('contact_type')
                ->options([
                    'Individual' => 'Individual',
                    'Business' => 'Business',
                    ])
                    ->searchable()
                    ->live()
                    ->required(),
                    Forms\Components\TextInput::make('contact')
                    ->tel()
                    ->required(),
                    
                    Forms\Components\TextInput::make('business_name')
                    ->visible(fn (Forms\Get $get) => $get("contact_type") == "Business")
                    ->maxLength(255),
                    Forms\Components\TextInput::make('email')
                    ->email(),
                    Forms\Components\Section::make('')
                    ->visible(fn (Forms\Get $get) => $get("contact_type") == "Individual")
                    ->description('')
                    ->schema([
                        Forms\Components\Select::make('title')
                        ->options([
                            'Mr' => 'Mr',
                            'Mrs' => 'Mrs',
                            'Ms' => 'Ms',
                            ])->required(),
                            Forms\Components\TextInput::make('firstname')
                            ->maxLength(255)
                            ->required(),
                            Forms\Components\TextInput::make('surname')
                            ->maxLength(255)
                            ->required(),
                            Forms\Components\TextInput::make('other_names')
                            ->maxLength(255),
                            
                            ])
                            ->columns(2),
                            
                            Forms\Components\TextInput::make('additional_contact')
                            ->maxLength(255),
                            Forms\Components\TextInput::make('landline')
                            ->tel()
                            ->maxLength(255),
                            
                            ])
                            ->columns(3),
                        ]);
                    }
                    
                    public static function table(Table $table): Table
                    {
                        return $table
                        ->columns([
                            Tables\Columns\TextColumn::make('contact_type')
                            ->searchable(),
                            Tables\Columns\TextColumn::make('contact')
                            ->searchable(),
                            Tables\Columns\TextColumn::make('title')
                            ->searchable(),
                            Tables\Columns\TextColumn::make('firstname')
                            ->searchable(),
                            Tables\Columns\TextColumn::make('surname')
                            ->searchable(),
                            Tables\Columns\TextColumn::make('other_names')
                            ->searchable(),
                            Tables\Columns\TextColumn::make('additional_contact')
                            ->searchable(),
                            Tables\Columns\TextColumn::make('landline_name')
                            ->searchable(),
                            Tables\Columns\TextColumn::make('business_name')
                            ->searchable(),
                            Tables\Columns\TextColumn::make('email')
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
                                        'index' => Pages\ListSuppliers::route('/'),
                                        'create' => Pages\CreateSupplier::route('/create'),
                                        'edit' => Pages\EditSupplier::route('/{record}/edit'),
                                    ];
                                }
                            }
                            