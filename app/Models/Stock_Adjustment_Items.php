<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Stock_Adjustment_Items extends Model
{
    use HasFactory;

    protected $guarded = ["id"];

    public function stockadjusment() {
       return $this->belongsTo(StockAdjustment::class,"stock_adjustment_id");
    }

    public function product() {
        return $this->belongsTo(Product::class,"product_id");
    }

    public function variant() {
        return $this->belongsTo(Product_Warehouse_Inventory::class,"variant_id");
    }
}
