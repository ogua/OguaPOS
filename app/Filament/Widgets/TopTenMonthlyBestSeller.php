<?php

namespace App\Filament\Widgets;

use App\Models\SalesItems;
use Filament\Widgets\Concerns\InteractsWithPageFilters;
use Filament\Widgets\Widget;
use Illuminate\Support\Facades\DB;

class TopTenMonthlyBestSeller extends Widget
{
    use InteractsWithPageFilters;

    protected static string $view = 'filament.widgets.top-ten-monthly-best-seller';

    protected static ?int $sort = 4;

    public $topseller;
    
   // protected int | string | array $columnSpan = 'full';

     /**
     * Widget Title
     *
     * @var string|null
     */
    protected static ?string $heading = 'Best Seller';

    public function mount()
    {
        $this->topseller = SalesItems::with('product')->select('product_name','product_id',DB::raw('sum(qty) as sold_qty'))
            ->whereDate('sales_items.created_at', '>=' , date("Y").'-01-01')
            ->whereDate('sales_items.created_at', '<=' , date("Y").'-12-31')
            // ->when($this->filters['warehouse_id'],function($query){
            //     return $query->where('warehouse_id',$this->filters['warehouse_id']);
            //     return $query;
            // })
            ->groupBy('product_id','variant_id','product_name')
            ->orderBy('sold_qty', 'desc')
            ->take(10)
            ->get();
    }
}
