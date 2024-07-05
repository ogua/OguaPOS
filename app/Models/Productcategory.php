<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Productcategory extends Model
{
    use HasFactory;

    protected $guarded = ["id"];

    public function products()
    {
        return $this->hasMany(Product::class,"");
    }

    public function owner(): BelongsTo
    {
        return $this->belongsTo(Warehouse::class,"warehouse_id");
    }
}
