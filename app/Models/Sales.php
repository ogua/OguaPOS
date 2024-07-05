<?php

namespace App\Models;

use App\Partials\Enums\PaymentStatus;
use App\Partials\Enums\PurchaseStatus;
use App\Partials\Enums\SalesStatus;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use SebastianBergmann\CodeCoverage\Report\Xml\Unit;

class Sales extends Model
{
    use HasFactory;

    protected $guarded = ["id"];


    protected $casts = [
        'payment_status' => PaymentStatus::class,
        'sale_status' => SalesStatus::class,
    ];

    public function saleitem() {
        return $this->hasMany(SalesItems::class,"sale_id");
    }

    public function payment() {
        return $this->hasOne(Payment::class,"sale_id");
    }

    public function payments() {
        return $this->hasMany(Payment::class,"sale_id");
    }

    public function customer() {
        return $this->belongsTo(Clients::class,"customer_id");
    }

    public function unit() {
        return $this->belongsTo(Productunit::class,"sale_unit_id");
    }

    public function user() {
        return $this->belongsTo(User::class,"biller_id");
    }

    public function biller() {
        return $this->belongsTo(Companyinfo::class,"biller_id");
    }

    public function delivery() {
        return $this->hasOne(Delivery::class,"sale_id");
    }

    public function warehouse() {
        return $this->belongsTo(Warehouse::class,"warehouse_id");
    }

    public function pos() {
        return $this->belongsTo(Possettings::class,"warehouse_id");
    }
}
