<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PurchaseReturnItems extends Model
{
    use HasFactory;

    protected $guarded = ["id"];

    public function returns() {
        return $this->belongsTo(PurchaseReturn::class,"purchase_return_id");
    }
}
