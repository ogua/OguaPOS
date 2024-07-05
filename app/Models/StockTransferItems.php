<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class StockTransferItems extends Model
{
    use HasFactory;

    protected $guarded = ["id"];

    public function stocktransfer() {
       return $this->belongsTo(StockTransfer::class,"stock_transfer_id");
    }

    public function product() {
        return $this->belongsTo(Product::class,"product_id");
    }

    public function variant() {
        return $this->belongsTo(Product_Warehouse_Inventory::class,"variant_id");
    }
}
