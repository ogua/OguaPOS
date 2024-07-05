<?php

namespace App\Models;

use App\Partials\Enums\DeliveryStatus;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class DeliveryHistory extends Model
{
    use HasFactory;

    protected $guarded = ["id"];

    protected $casts = [
        'from_statues' => DeliveryStatus::class,
        'to_statues' => DeliveryStatus::class
    ];

    public function user()
    {
        return $this->belongsTo(User::class,"edited_by"); 
    }
}
