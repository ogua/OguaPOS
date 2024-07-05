<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Deals extends Model
{
    use HasFactory;

    protected $guarded = ["id"];

    public function dealitems()
    {
        return $this->hasMany(Dealsproduct::class,"deals_id");   
    }

    public function client()
    {
        return $this->belongsTo(Clients::class,"client_id");   
    }
}
