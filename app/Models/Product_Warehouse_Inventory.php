<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Product_Warehouse_Inventory extends Model
{
    use HasFactory;

    protected $guarded = ["id"];

    public function product()
    {
        return $this->belongsTo(Product::class, 'product_id');
    }

    public function variant()
    {
        return $this->belongsTo(Product_variation::class, 'variant_id');
    }

    public function warehouse()
    {
        return $this->belongsTo(Warehouse::class, 'warehouse_id');
    }

    public function variantitems()
    {
        return $this->hasMany(SalesItems::class, 'variant_id',"variant_id");
    }

    public function singleitems()
    {
        return $this->hasMany(SalesItems::class, 'product_id',"product_id");
    }

    public function variantunittransfer()
    {
        return $this->hasMany(StockTransferItems::class, 'variant_id',"variant_id");
    }

    public function singleunittransfer()
    {
        return $this->hasMany(StockTransferItems::class, 'product_id',"product_id");
    }

     public function variantunitadjusted()
    {
        return $this->hasMany(Stock_Adjustment_Items::class, 'variant_id',"variant_id");
    }

    public function singleunitadjusted()
    {
        return $this->hasMany(Stock_Adjustment_Items::class, 'product_id',"product_id");
    }


}
