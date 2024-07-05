<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Stockcount extends Model
{
    use HasFactory;

    protected $guarded = ["id"];


    public function warehouse()
    {
        return $this->belongsTo(Warehouse::class,"warehouse_id");
    }

    public function category()
    {
        return $this->belongsTo(Productcategory::class,"category_id");
    }

    public function brand()
    {
        return $this->belongsTo(Brand::class,"brand_id");
    }


}
