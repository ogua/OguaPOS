<?php

namespace App\Models;

use App\Partials\Enums\PaymentStatus;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class SaleReturn extends Model
{
    use HasFactory;

    protected $guarded = ["id"];

    protected $casts = [
        'payment_status' => PaymentStatus::class
    ];

    public function saleitems() {
        return $this->hasMany(SalesItems::class,"sale_id");
    }

    public function sale() {
        return $this->belongsTo(Sales::class,"sale_id");
    }

    public function returnitems() {
        return $this->hasMany(SaleReturnItem::class,"sale_return_id");
    }

    public function cpayments() {
        return $this->hasMany(SaleReturnPayment::class,"sale_return_id");
    }

    public function payments() {
        return $this->hasMany(Payment::class,"sale_return_id");
    }
}
