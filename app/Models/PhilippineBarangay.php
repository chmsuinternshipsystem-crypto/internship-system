<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PhilippineBarangay extends Model
{
    protected $fillable = ['code', 'name', 'city_id'];

    public function city(): BelongsTo
    {
        return $this->belongsTo(PhilippineCity::class, 'city_id');
    }
}
