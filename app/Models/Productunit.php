<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Productunit extends Model
{
    use HasFactory;

    protected $guarded = ["id"];

    public function products() {
        return $this->hasMany(Product::class,"product_unit_id");
    }

    public function unit() {
        return $this->belongsTo(Productunit::class,"base_unit");
    }
}
