<?php

namespace App\Models;

use App\Partials\Enums\PaymentStatus;
use App\Partials\Enums\PurchaseStatus;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Purchase extends Model
{
    use HasFactory;

    protected $guarded = ["id"];

    protected $casts = [
        'purchase_status' => PurchaseStatus::class,
        'payment_status' => PaymentStatus::class
    ];

    public function payments() {
        return $this->hasMany(Payment::class,"purchase_id");
    }

    public function purchaseitmes() {
        return $this->hasMany(PurchaseItems::class,"purchase_id");
    }

    public function product() {
        return $this->belongsTo(Product::class,"product_id");
    }

    public function variant() {
        return $this->belongsTo(Variation::class,"variant_id");
    }

    public function purchasetax()
    {
        return $this->belongsToMany(Taxrates::class,'purchase_discounts','purchase_id','tax_id');
    }

    public function puchaseexpenses() {
        return $this->hasMany(PurchaseExpense::class,"purchase_id");
    }

    public function suplier() {
        return $this->belongsTo(Supplier::class,"suppier_id");
    }

    public function warehouse() {
        return $this->belongsTo(Warehouse::class,"warehouse_id");
    }

     public function user() {
        return $this->belongsTo(User::class,"user_id");
    }
    

    
}
