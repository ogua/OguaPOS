<?php

namespace App\Filament\Resources\ProductResource\Pages;

use App\Filament\Resources\ProductResource;
use App\Filament\Resources\ProductResource\Widgets\ProductStatistics;
use App\Models\Product;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;
use Filament\Resources\Components\Tab;
use Illuminate\Database\Eloquent\Builder;

class ListProducts extends ListRecords
{
    protected static string $resource = ProductResource::class;

    public function getTabs(): array
    {   
        return [
            'all' => Tab::make('All Products'),
            'Single' => Tab::make('Single Prodcts')
                ->modifyQueryUsing(fn (Builder $query) => $query->where('product_type', 'Single')),
            'Variation' => Tab::make('Variation Prodcts')
                ->modifyQueryUsing(fn (Builder $query) => $query->where('product_type', 'Variation')),
            'Combo' => Tab::make('Combo Prodcts')
                ->modifyQueryUsing(fn (Builder $query) => $query->where('product_type', 'Combo')),
        ];
    }

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }

    protected function getHeaderWidgets(): array
    {
        return [
           ProductStatistics::class
        ];
    }

    
}
