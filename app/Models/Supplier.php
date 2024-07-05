<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Supplier extends Model
{
    use HasFactory;

    protected $guarded = ["id"];

    public function getFullnameAttribute(){
        if ($this->contact_type == "Individual") {
            return "{$this->title} {$this->surname} {$this->firstname} {$this->other_names}";
        }else{
            return "{$this->business_name}";
        }
    }
}
