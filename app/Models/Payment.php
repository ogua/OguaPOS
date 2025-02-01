<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Payment extends Model
{
    use HasFactory;

    protected $guarded = ["id"];

    protected $casts = [
       // 'paid_on' => 'date'
    ];

    public function account() {
        return $this->belongsTo(PaymentAccount::class,"account_id");
    }

    public function user()
    {
        return $this->belongsTo(User::class,"user_id");
    }

    public function purchase()
    {
        return $this->belongsTo(Purchase::class,"purchase_id");
    }

    public function sale()
    {
        return $this->belongsTo(Sales::class,"sale_id");
    }

    public function salereturn()
    {
        return $this->belongsTo(SaleReturn::class,"sale_return_id");
    }


    public function purchasereturn()
    {
        return $this->belongsTo(PurchaseReturn::class,"purchase_return_id");
    }

    public function cashregister()
    {
        return $this->belongsTo(Cashregister::class,"cash_register_id");
    }

    public function fundtransfer()
    {
        return $this->belongsTo(FundTransfer::class,"fund_transfer_id");
    }

}
