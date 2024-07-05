<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Product_Warehouse extends Model
{
    use HasFactory;

    protected $guarded = ["id"];

    public function inventory() {
        return $this->hasMany(Product_Warehouse_Inventory::class);
    }
}
