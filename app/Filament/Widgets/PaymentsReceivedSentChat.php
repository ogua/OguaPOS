<?php

namespace App\Filament\Widgets;

use App\Models\Payment;
use Carbon\Carbon;
use Filament\Widgets\Concerns\InteractsWithPageFilters;
use Illuminate\Support\Facades\DB;
use Leandrocfe\FilamentApexCharts\Widgets\ApexChartWidget;

class PaymentsReceivedSentChat extends ApexChartWidget
{
    use InteractsWithPageFilters;
    /**
     * Chart Id
     *
     * @var string
     */
    protected static ?string $chartId = 'paymentsReceivedSentChat';

    protected static ?int $sort = 2;

    /**
     * Widget Title
     *
     * @var string|null
     */
    protected static ?string $heading = '';

    /**
     * Chart options (series, labels, types, size, animations...)
     * https://apexcharts.com/docs/options
     *
     * @return array
     */
    protected function getOptions(): array
    {
        // Define your date range
       // $start_date = is_null($this->filters['startDate']) ? date('Y-m-d') : $this->filters['startDate'];
       // $end_date = is_null($this->filters['endDate']) ? date('Y-m-d') : $this->filters['startDate'];

        $start_date = Carbon::now()->startOfMonth();
        $end_date =  Carbon::now()->endOfMonth();

        // Query to get total sales for each month within the date range
        $sales_data = Payment::whereDate('created_at', '>=', $start_date)
             ->where(function ($query) {
                $query->whereNotNull('sale_id')
                    ->orWhereNotNull('purchase_return_id')
                    ->orWhereNotNull('fund_transfer_id');
            })
            ->whereDate('created_at', '<=', $end_date)
            ->when($this->filters['warehouse_id'],function($query){
                return $query;
                //return $query->where('warehouse_id',$this->filters['warehouse_id']);
            })
            ->select(
                DB::raw('MONTH(created_at) as month'),
                DB::raw('SUM(amount) as total_sales')
            )
            ->groupBy(DB::raw('MONTH(created_at)'))
            ->get();

            // Create an array to hold sales data formatted for the chart
            $sales_chart_data = array_fill(0, 12, 0);

            // Populate the sales data for the corresponding months
            foreach ($sales_data as $data) {
                $month_index = $data->month - 1; // Convert 1-12 to 0-11 index
                $sales_chart_data[$month_index] = $data->total_sales;
            }



        // Query to get total sales for each month within the date range
        $purchase_data = Payment::whereDate('created_at', '>=', $start_date)
            ->whereDate('created_at', '<=', $end_date)
            ->where(function ($query) {
                $query->whereNotNull('purchase_id')
                    ->orWhereNotNull('sale_return_id');
            })
            ->when($this->filters['warehouse_id'],function($query){
                return $query;
                //return $query->where('warehouse_id',$this->filters['warehouse_id']);
            })
            ->select(
                DB::raw('MONTH(created_at) as month'),
                DB::raw('SUM(amount) as total_purchase')
            )
            ->groupBy(DB::raw('MONTH(created_at)'))
            ->get();

            // Create an array to hold sales data formatted for the chart
            $purchases_chart_data = array_fill(0, 12, 0);

            // Populate the sales data for the corresponding months
            foreach ($purchase_data as $data) {
                $month_index = $data->month - 1; // Convert 1-12 to 0-11 index
                $purchases_chart_data[$month_index] = $data->total_purchase;
            }
            
            
        
        
        return [
            'chart' => [
                'type' => 'line',
                'height' => 280,
                'parentHeightOffset' => 2,
                'stacked' => true,
                'toolbar' => [
                    'show' => false,
                ],
            ],
            'series' => [
                [
                    'name' => 'PAYMENTS RECEIVED',
                    'data' => $sales_chart_data
                ],
                [
                    'name' => 'PAYMENTS SENT',
                    'data' => $purchases_chart_data
                ],
            ],
            'plotOptions' => [
                'bar' => [
                    'horizontal' => false,
                    'columnWidth' => '50%',
                ],
            ],
            'dataLabels' => [
                'enabled' => false
            ],
            'legend' => [
                'show' => true,
                'horizontalAlign' => 'right',
                'position' => 'top',
                'fontFamily' => 'inherit',
                'markers' => [
                    'height' => 12,
                    'width' => 12,
                    'radius' => 12,
                    'offsetX' => -3,
                    'offsetY' => 2
                ],
                'itemMargin' => [
                    'horizontal' => 5
                ]
            ],
            'grid' => [
                'show' => false,

            ],
            'xaxis' => [
                'categories' => [
                    'Jan', 'Feb', 'Mar', 'Apr', 'May', 'Jun', 'Jul', 'Aug', 'Sep', 'Oct', 'Nov', 'Dec'
                ],
                'labels' => [
                    'style' => [
                        'fontFamily' => 'inherit'
                    ]
                ],
                'axisTicks' => [
                    'show' => false
                ],
                'axisBorder' => [
                    'show' => false
                ]
            ],
            'yaxis' => [
                'offsetX' => -16,
                'labels' => [
                    'style' => [
                        'fontFamily' => 'inherit'
                    ]
                ],
            ],
            'fill' => [
                'type' => 'gradient',
                'gradient' => [
                    'shade' => 'dark',
                    'type' => 'vertical',
                    'shadeIntensity' => 0.5,
                    'gradientToColors' => ['#d97706', '#c2410c'],
                    'opacityFrom' => 1,
                    'opacityTo' => 1,
                    'stops' => [0, 100],
                ],
            ],
            'stroke' => [
                'curve' => 'smooth',
                'width' => 1,
                'lineCap' => 'round'
            ],
            'colors' => ['#f59e0b', '#ea580c'],
        ];
    }
}
