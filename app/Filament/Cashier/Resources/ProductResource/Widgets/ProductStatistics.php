<?php

namespace App\Filament\Cashier\Resources\ProductResource\Widgets;

use App\Models\Product;
use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;

class ProductStatistics extends BaseWidget
{
    protected function getStats(): array
    {

        $singleproducts = Product::where('product_type', 'Single')->count();
        $variation = Product::where('product_type', 'Variation')->count();
        $combo = Product::where('product_type', 'Combo')->count();

        
        
        return [
            Stat::make('Single Products',$singleproducts)
            ->description('Total Single Products')
            ->descriptionIcon('heroicon-m-clipboard-document-list')
            ->color('success'),
            Stat::make('Variant Products',$variation)
                ->description('Total Variant Products')
                ->descriptionIcon('heroicon-m-clipboard-document-list')
                ->color('info'),
            Stat::make('Combo Products',$combo)
                ->description('Total Combo Products')
                ->descriptionIcon('heroicon-m-clipboard-document-list')
                ->color('danger')
        ];
    }
}
