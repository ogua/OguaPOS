<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Variation extends Model
{
    use HasFactory;

    protected $guarded = ["id"];

    public function variationvalues() : HasMany {
        return $this->hasMany(Variablevalue::class,"variation_id");
    }
}
