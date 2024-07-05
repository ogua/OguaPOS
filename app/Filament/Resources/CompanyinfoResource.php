<?php

namespace App\Filament\Resources;

use App\Filament\Resources\CompanyinfoResource\Pages;
use App\Filament\Resources\CompanyinfoResource\RelationManagers;
use App\Models\Companyinfo;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class CompanyinfoResource extends Resource
{
    protected static ?string $model = Companyinfo::class;

    protected static ?string $navigationIcon = 'heroicon-o-home-modern';

    protected static ?string $slug = 'settings/company-info';
    protected static ?string $navigationGroup = 'Settings';
    protected static ?string $navigationLabel = 'Company info';
    protected static ?string $modelLabel = 'Company info';
    protected static ?int $navigationSort = 1;

    public static function form(Form $form): Form
    {
        return $form
            ->schema([

                Forms\Components\Section::make('')
                    ->description('')
                    ->schema([
                Forms\Components\FileUpload::make('logo')
                ->image()
                ->imageEditor()
                ->circleCropper()
                ->uploadingMessage('Uploading logo...')
                ->columnSpanFull()
                ->required(),
                Forms\Components\TextInput::make('name')
                ->columnSpanFull()
                    ->required()
                    ->maxLength(255),
                Forms\Components\Select::make('companytype')
                ->label('Company type')
                ->options([
                    'Sole proprietorship' => 'Sole proprietorship',
                    'Limited Liability' => 'Limited Liability'
                ])
                    ->required(),
                Forms\Components\TextInput::make('location')
                    ->required()
                    ->maxLength(255),
                Forms\Components\Textarea::make('address')
                    ->required()
                    ->maxLength(65535)
                    ->columnSpanFull(),
                Forms\Components\TextInput::make('email1')
                   ->label('Company email 1')
                    ->email()
                    ->required()
                    ->maxLength(255),
                Forms\Components\TextInput::make('other_email')
                    ->label('Company email 2')
                    ->email()
                    ->maxLength(255),
                Forms\Components\TextInput::make('phone')
                    ->label('Company contact 1')
                    ->tel()
                    ->required()
                    ->maxLength(255),
                Forms\Components\TextInput::make('other_phone')
                    ->label('Company contact 1')
                    ->tel()
                    ->maxLength(255),
                Forms\Components\Textarea::make('description')
                    ->maxLength(65535)
                    ->columnSpanFull(),
                    ])
                    ->columns(2)
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\ImageColumn::make('logo')
                    ->label('Logo')
                    ->circular()
                    //->defaultImageUrl(url('/images/user.png'))
                    ->width(80),
                Tables\Columns\TextColumn::make('name')
                    ->searchable(),
                Tables\Columns\TextColumn::make('companytype')
                    ->searchable(),
                Tables\Columns\TextColumn::make('location')
                    ->searchable(),
                Tables\Columns\TextColumn::make('email1')
                    ->searchable(),
                Tables\Columns\TextColumn::make('other_email')
                    ->searchable(),
                Tables\Columns\TextColumn::make('phone')
                    ->searchable(),
                Tables\Columns\TextColumn::make('other_phone')
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
                Tables\Actions\ViewAction::make(),
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
            'index' => Pages\ListCompanyinfos::route('/'),
            'create' => Pages\CreateCompanyinfo::route('/create'),
            'view' => Pages\ViewCompanyinfo::route('/{record}'),
            'edit' => Pages\EditCompanyinfo::route('/{record}/edit'),
        ];
    }
}
