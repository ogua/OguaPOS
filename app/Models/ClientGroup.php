<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ClientGroup extends Model
{
    use HasFactory;

    protected $guarded = ["id"];

    public function client() {
        
        return $this->hasOne(ClientGroup::class,"client_group_id");
    }
}
