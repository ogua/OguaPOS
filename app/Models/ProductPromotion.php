<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ProductPromotion extends Model
{
    use HasFactory;

    protected $guarded = ["id"];

    public function warehouse() {
        return $this->belongsTo(Warehouse::class,"warehouse_id");
    }

    public function scopeActivepromo($query)
    {
        return $query->where('status',true);
    }

    public function scopeCurrentdate($query)
    {
        return $query->where('start_date','<=', now())
        ->where('end_date','>=', now());
    }

}
