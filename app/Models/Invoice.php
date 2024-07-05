<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Invoice extends Model
{
    use HasFactory;

    protected $guarded = ["id"];

    public function orderitems()
    {
        return $this->hasMany(InvoiceItems::class,"invoice_id");   
    }

    public function client()
    {
        return $this->belongsTo(Clients::class,"client_id");   
    }
}
