<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Delivery extends Model
{
    use HasFactory;

    protected $guarded = ["id"];
    

    public function order()
    {
        return $this->belongsTo(Sales::class,"sale_id"); 
    }

    public function client()
    {
        return $this->belongsTo(Clients::class,"client_id");   
    }

    public function deliveryhistort() {
        return $this->hasMany(DeliveryHistory::class,"delivery_id")
        ->latest();
    }
}
