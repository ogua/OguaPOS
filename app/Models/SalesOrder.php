<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class SalesOrder extends Model
{
    use HasFactory;

    protected $guarded = ["id"];

    public function orderitems()
    {
        return $this->hasMany(OrderItem::class,"order_id");   
    }

    public function shipping()
    {
        return $this->hasOne(Shipping::class,"order_id"); 
    }

    public function billing()
    {
        return $this->hasOne(Address::class,"order_id"); 
    }

    public function client()
    {
        return $this->belongsTo(Clients::class,"client_id");   
    }

    public function delivery()
    {
        return $this->hasOne(Deliveries::class,"order_id"); 
    }
}
