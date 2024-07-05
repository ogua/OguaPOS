<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class SalesItems extends Model
{
    use HasFactory;

    protected $guarded = ["id"];


    public function sale() {
        return $this->belongsTo(Sales::class,"sale_id");
    }
    
    public function product() {
        return $this->belongsTo(Product::class,"product_id");
    }

    public function unit() {
        return $this->belongsTo(Productunit::class,"sale_unit_id");
    }
}
