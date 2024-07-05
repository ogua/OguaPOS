<?php

namespace App\Models;

use App\Partials\Enums\PaymentStatus;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PurchaseReturn extends Model
{
    use HasFactory;

    protected $guarded = ["id"];

    protected $casts = [
        'payment_status' => PaymentStatus::class
    ];

    public function saleitems() {
        return $this->hasMany(PurchaseItems::class,"sale_id");
    }

    public function sale() {
        return $this->belongsTo(Purchase::class,"puchase_id");
    }

    public function purchase() {
        return $this->belongsTo(Purchase::class,"puchase_id");
    }

    public function returnitems() {
        return $this->hasMany(PurchaseReturnItems::class,"purchase_return_id");
    }

    public function cpayments() {
        return $this->hasMany(SaleReturnPayment::class,"purchase_return_id");
    }

    public function payments() {
        return $this->hasMany(Payment::class,"purchase_return_id");
    }
}
