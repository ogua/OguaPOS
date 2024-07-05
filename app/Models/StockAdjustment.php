<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class StockAdjustment extends Model
{
    use HasFactory;

     protected $guarded = ["id"];

    public function stockitems() {
        return $this->hasMany(Stock_Adjustment_Items::class,"stock_adjustment_id");
    }

    public function warehouse() {
        return $this->belongsTo(Warehouse::class,"warehouse_id");
    }

    public function user() {
        return $this->belongsTo(User::class,"user_id");
    }
}
