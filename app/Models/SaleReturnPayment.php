<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class SaleReturnPayment extends Model
{
    use HasFactory;

    protected $guarded = ["id"];

    public function account() {
        return $this->belongsTo(PaymentAccount::class,"account_id");
    }

    public function user() {
        return $this->belongsTo(User::class,"user_id");
    }

    public function return() {
        return $this->belongsTo(SaleReturn::class,"sale_id");
    }
}
