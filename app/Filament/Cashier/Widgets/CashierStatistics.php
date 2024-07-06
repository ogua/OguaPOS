<?php

namespace App\Filament\Cashier\Widgets;

use App\Models\Cashregister;
use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;
use App\Models\Payment;
use App\Models\Product;
use App\Models\Purchase;
use App\Models\PurchaseReturn;
use App\Models\SaleReturn;
use App\Models\Sales;
use App\Models\SalesItems;
use Carbon\Carbon;
use Filament\Widgets\Concerns\InteractsWithPageFilters;

class CashierStatistics extends BaseWidget
{
    use InteractsWithPageFilters;
    
    protected static ?int $sort = 1;

    // protected int | string | array $columnSpan = 'full';

    protected function getStats(): array
    {
        $start_date = !is_null($this->filters['startDate'] ?? null) ? Carbon::parse($this->filters['startDate']) : Carbon::now()->startOfMonth();
        $end_date = !is_null($this->filters['endDate'] ?? null) ? Carbon::parse($this->filters['endDate']) : Carbon::now()->endOfMonth();

        $product_sales = Sales::whereDate('created_at', '>=', $start_date)
            ->whereDate('created_at', '<=', $end_date)
            ->where('user_id',auth()->user()->id)
            ->with(['saleitem.product.inventory']) // Eager load related models
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
            ->where('user_id',auth()->user()->id)
            ->sum('grand_total');

        $return = SaleReturn::whereDate('created_at', '>=', $start_date)
            ->whereDate('created_at', '<=', $end_date)
            ->where('user_id',auth()->user()->id)
            ->sum('grand_total');

        $purchase_return = PurchaseReturn::whereDate('created_at', '>=', $start_date)
            ->whereDate('created_at', '<=', $end_date)
            ->where('user_id',auth()->user()->id)
            ->sum('grand_total');

        $revenue -= $return;

        $profit = $revenue + $purchase_return - $total_cost;

        $cashreg = Cashregister::where('user_id',auth()->user()->id)
        ->where('status',true)
        ->latest()
        //->where('warehouse_id',$warehouse_id)
        ->first();

        $totalsales = ($cashreg->cash_in_hand ?? 0) + $revenue;
  
        return [
            Stat::make('Revenue', "GHC ".number_format($revenue,2))
            ->description('Total Revenue')
            ->descriptionIcon('heroicon-m-chart-bar-square')
            ->color('success'),
            Stat::make('Cash at hand', "GHC ".number_format($cashreg->cash_in_hand ?? 0,2))
                ->description('Cash At Hand')
                ->descriptionIcon('heroicon-m-arrow-uturn-left')
                ->color('info'),
            Stat::make('Total Sales', "GHC ".number_format($totalsales,2))
                ->description('Total Sales')
                ->descriptionIcon('heroicon-m-arrow-right-start-on-rectangle')
                ->color('danger'),
            Stat::make('Profit Made', "GHC ".number_format($profit,2))
                ->description('Total Profit')
                ->descriptionIcon('heroicon-m-trophy')
                ->color('warning'),
        ];
    }
}
