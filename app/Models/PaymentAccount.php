<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PaymentAccount extends Model
{
    use HasFactory;

    protected $guarded = ["id"];

    protected $casts = [
        'account_details' => 'array',
    ];

    public function user() {
        return $this->belongsTo(User::class,"user_id");
    }

    public function accounttransactioninfo() {
        return $this->hasMany(FundTransfer::class,"user_id");
    }
}
