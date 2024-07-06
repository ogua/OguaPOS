<?php

namespace App\Filament\Cashier\Resources\ProductResource\Pages;

use App\Filament\Cashier\Resources\ProductResource;
use App\Models\Product;
use Filament\Resources\Pages\Page;

class ProductHistory extends Page
{
    protected static string $resource = ProductResource::class;

    protected static string $view = 'filament.resources.product-resource.pages.product-history';

    public $data;
    public $warehouse;
    public $variation;
    public $history;


    public function mount($record) {

        $this->data = Product::where('id',$record)
        ->first();
        $this->warehouse = $this->data?->warehouses[0]?->id ?? 0;
        $this->variation = $this->data?->variationitems[0]?->id ?? 0;

        if ($this->data->product_type == "Variation") {
            $this->history = $this->data?->history->where('variant_id', $this->variation)
            ->where('warehouse_id',$this->warehouse);
        }elseif ($this->data->product_type == "Single") {
            $this->history = $this->data?->history->where('warehouse_id',$this->warehouse);
        }
    }


    public function changewarehouse() {
        $this->reload();
    }

    public function changevariation() {
        $this->reload();
    }

    public function reload() {

        if ($this->data->product_type == "Variation") {
            $this->history = $this->data?->history->where('variant_id', $this->variation)
            ->where('warehouse_id',$this->warehouse);
        }elseif ($this->data->product_type == "Single") {
            $this->history = $this->data?->history->where('warehouse_id',$this->warehouse);
        }
    }
}
