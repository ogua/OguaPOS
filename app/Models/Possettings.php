<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Possettings extends Model
{
    use HasFactory;

    protected $guarded = ["id"];

    public function customer()
    {
        return $this->belongsTo(Clients::class,"customer_id");   
    }

    public function warehouse()
    {
        return $this->belongsTo(Warehouse::class,"warehouse_id");   
    }

    public function user()
    {
        return $this->belongsTo(User::class,"user_id");   
    }


    public function company()
    {
        return $this->belongsTo(Companyinfo::class,"biller_id");   
    }

    public function currncy()
    {
        return $this->belongsTo(Currency::class,"currency");
    }
}
