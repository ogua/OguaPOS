<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Variablevalue extends Model
{
    use HasFactory;

    protected $guarded = ["id"];

    public function FunctionName() : BelongsTo {
        return $this->belongsTo(Variation::class,"variation_id");
    }
}
