<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Cashregister extends Model
{
    use HasFactory;

    protected $guarded = ["id"];


    public function warehouse()
    {
        return $this->belongsTo(Warehouse::class,"warehouse_id");
    }
}
