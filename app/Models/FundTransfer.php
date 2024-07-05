<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class FundTransfer extends Model
{
    use HasFactory;

    protected $guarded = ["id"];

    public function user() {
        return $this->belongsTo(User::class,"user_id");
    }

    public function accfrom() {
        return $this->belongsTo(PaymentAccount::class,"transfer_from");
    }

    public function accto() {
        return $this->belongsTo(PaymentAccount::class,"transfer_to");
    }
    
}
