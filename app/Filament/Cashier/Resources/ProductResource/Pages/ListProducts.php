<?php

namespace App\Filament\Cashier\Resources\ProductResource\Pages;

use App\Filament\Cashier\Resources\ProductResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;
use App\Filament\Cashier\Resources\ProductResource\Widgets\ProductStatistics;
use App\Models\Product;
use Filament\Resources\Components\Tab;
use Illuminate\Database\Eloquent\Builder;

class ListProducts extends ListRecords
{
    protected static string $resource = ProductResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }

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


    protected function getHeaderWidgets(): array
    {
        return [
           ProductStatistics::class
        ];
    }
}
