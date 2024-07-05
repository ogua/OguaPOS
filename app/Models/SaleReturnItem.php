<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class SaleReturnItem extends Model
{
    use HasFactory;

    protected $guarded = ["id"];

    public function returns() {
        return $this->belongsTo(SaleReturn::class,"sale_return_id");
    }
}
