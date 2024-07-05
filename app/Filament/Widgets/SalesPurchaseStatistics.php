<?php

namespace App\Filament\Widgets;

use App\Models\Payment;
use App\Models\Product;
use App\Models\Purchase;
use App\Models\PurchaseReturn;
use App\Models\SaleReturn;
use App\Models\Sales;
use App\Models\SalesItems;
use Carbon\Carbon;
use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;
use Filament\Widgets\Concerns\InteractsWithPageFilters;

class SalesPurchaseStatistics extends BaseWidget
{
    use InteractsWithPageFilters;
    
    protected static ?int $sort = 1;

    // protected int | string | array $columnSpan = 'full';

    protected function getStats(): array
    {
        //$start_date = !is_null($this->filters['startDate'] ?? null) ? Carbon::parse($this->filters['startDate']) : Carbon::now()->startOfMonth();
        //$end_date = !is_null($this->filters['endDate'] ?? null) ? Carbon::parse($this->filters['endDate']) : Carbon::now()->endOfMonth();

        $start_date = is_null($this->filters['startDate']) ? date('Y-m-d') : $this->filters['startDate'];
        $end_date = is_null($this->filters['endDate']) ? date('Y-m-d') : $this->filters['startDate'];

        //logger($this->filters['startDate'] ?? "null");
        //logger($this->filters['endDate'] ?? "null");

        $product_sales = Sales::with('saleitem')->whereDate('created_at', '>=', $start_date)
            ->whereDate('created_at', '<=', $end_date)
            ->with(['saleitem.product.inventory']) // Eager load related models
            ->when($this->filters['warehouse_id'],function($query){
                return $query->where('warehouse_id',$this->filters['warehouse_id']);
            })
            ->get();

        $total_cost = 0;

        foreach ($product_sales as $sale) {
            $warehouse_id = $sale->warehouse_id;

            foreach ($sale->saleitem as $product_sale) {
                $productid = $product_sale->product_id;
                $variant = $product_sale->variant_id;
                $qty = $product_sale->qty;

                $costpx = 0;

                if ($productid && $variant) {
                    $product_variant_data = $product_sale->product->inventory()
                        ->where('variant_id', $variant)
                        ->where('warehouse_id', $warehouse_id)
                        ->first();
                } elseif ($productid && is_null($variant)) {
                    $product_variant_data = $product_sale->product->inventory()
                        ->where('warehouse_id', $warehouse_id)
                        ->first();
                }

                if (isset($product_variant_data)) {
                    $costpx = $product_variant_data->cost_price;
                }

                $totalpx = $costpx * $qty;

                $total_cost += $totalpx;
            }
        }

        $revenue = Sales::whereDate('created_at', '>=', $start_date)
            ->whereDate('created_at', '<=', $end_date)
            ->when($this->filters['warehouse_id'],function($query){
                return $query->where('warehouse_id',$this->filters['warehouse_id']);
            })
            ->sum('grand_total');

        $return = SaleReturn::whereDate('created_at', '>=', $start_date)
            ->whereDate('created_at', '<=', $end_date)
            ->when($this->filters['warehouse_id'],function($query){
                //return $query->where('warehouse_id',$this->filters['warehouse_id']);
                return $query;
            })
            ->sum('grand_total');

        $purchase_return = PurchaseReturn::whereDate('created_at', '>=', $start_date)
            ->whereDate('created_at', '<=', $end_date)
            ->when($this->filters['warehouse_id'],function($query){
               // return $query->where('warehouse_id',$this->filters['warehouse_id']);
                return $query;
            })
            ->sum('grand_total');

        $revenue -= $return;

        $profit = $revenue + $purchase_return - $total_cost;
        
        
        return [
            Stat::make('Revenue', "GHC ".number_format($revenue,2))
            ->description('Total Revenue')
            ->descriptionIcon('heroicon-m-chart-bar-square')
            ->color('success'),
            Stat::make('Sales Return', "GHC ".number_format($return,2))
                ->description('Total Sales Return')
                ->descriptionIcon('heroicon-m-arrow-uturn-left')
                ->color('info'),
            Stat::make('Purchase Return', "GHC ".number_format($purchase_return,2))
                ->description('Total Purchase Return')
                ->descriptionIcon('heroicon-m-arrow-right-start-on-rectangle')
                ->color('danger'),
            Stat::make('Profit', "GHC ".number_format($profit,2))
                ->description('Total Profit')
                ->descriptionIcon('heroicon-m-trophy')
                ->color('warning'),
        ];
    }
}
