<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Clients extends Model
{
    use HasFactory;

    protected $guarded = ["id"];

    public function group() {
        return $this->belongsTo(ClientGroup::class,"client_group_id");
    }
}
