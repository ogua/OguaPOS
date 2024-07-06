<?php

namespace App\Filament\Cashier\Resources;

use App\Filament\Cashier\Resources\BrandResource\Pages;
use App\Filament\Cashier\Resources\BrandResource\RelationManagers;
use App\Models\Brand;
use Filament\Forms;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Form;
use Filament\Forms\Get;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Columns\Layout\Grid;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class BrandResource extends Resource
{
    protected static ?string $model = Brand::class;
    
    protected static ?string $navigationIcon = 'heroicon-o-bars-arrow-down';
    
    protected static ?string $slug = 'products/brand';
    protected static ?string $navigationGroup = 'Products';
    protected static ?string $navigationLabel = 'Brand';
    protected static ?string $modelLabel = 'Brand';
    protected static ?int $navigationSort = 5;
    
    public static function form(Form $form): Form
    {
        return $form
        ->schema([
            Forms\Components\Section::make('')
            ->description('')
            ->schema([
                Forms\Components\FileUpload::make('brand_image')
                ->image()
                ->columnSpanFull(),
                Forms\Components\TextInput::make('name')
                ->required()
                ->live()
                ->dehydrateStateUsing(fn (string $state): string => ucwords($state))
                ->maxLength(255),
                
                Forms\Components\Select::make('type')
                ->options([
                    'employee' => 'Employee',
                    'freelancer' => 'Freelancer',
                    ])
                    ->live()
                    ->afterStateUpdated(fn (Select $component) => $component
                    ->getContainer()
                    ->getComponent('dynamicTypeFields')
                    ->getChildComponentContainer()
                    ->fill()),
                    
                    Forms\Components\Grid::make(2)
                    ->schema(fn (Get $get): array => match ($get('type')) {
                        'employee' => [
                            Forms\Components\TextInput::make('employee_number')
                            ->required(),
                            Forms\Components\FileUpload::make('badge')
                            ->image()
                            ->required(),
                        ],
                        'freelancer' => [
                            Forms\Components\TextInput::make('hourly_rate')
                            ->numeric()
                            ->required()
                            ->prefix('€'),
                           Forms\Components\FileUpload::make('contract')
                            ->required(),
                        ],
                        default => [],
                    })
                    ->key('dynamicTypeFields')
                    
                    
                    
                    
                    
                    
                    ])
                    ->columns(2),
                ]);
            }
            
            public static function table(Table $table): Table
            {
                return $table
                ->columns([
                    Tables\Columns\ImageColumn::make('brand_image'),
                    Tables\Columns\TextColumn::make('name')
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
                                'index' => Pages\ListBrands::route('/'),
                                'create' => Pages\CreateBrand::route('/create'),
                                'edit' => Pages\EditBrand::route('/{record}/edit'),
                            ];
                        }
                    }
                    