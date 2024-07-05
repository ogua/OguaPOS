<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Warehouse extends Model
{
    use HasFactory;

    protected $guarded = ["id"];

    public function products()
    {
        return $this->hasMany(Product::class);
    }

    public function productcategories()
    {
        return $this->hasMany(Productcategory::class);
    }
}
