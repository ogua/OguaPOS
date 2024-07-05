<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Deliveries extends Model
{
    use HasFactory;

    protected $guarded = ["id"];

    public function order()
    {
        return $this->belongsTo(SalesOrder::class,"order_id"); 
    }

    
    public function client()
    {
        return $this->belongsTo(Clients::class,"client_id");   
    }
    
}
