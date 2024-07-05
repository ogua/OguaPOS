<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class StockTransfer extends Model
{
    use HasFactory;

    protected $guarded = ["id"];

    protected $casts = [
        'date' => 'date'
    ];

    public function transferitems() {
        return $this->hasMany(StockTransferItems::class,"stock_transfer_id");
    }

    public function fromwarehouse() {
        return $this->belongsTo(Warehouse::class,"from_warehouse_id");
    }

    public function towarehouse() {
        return $this->belongsTo(Warehouse::class,"to_warehouse_id");
    }
}
