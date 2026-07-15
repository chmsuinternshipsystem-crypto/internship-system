<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class PhilippineProvince extends Model
{
    protected $fillable = ['code', 'name', 'region'];

    public function cities(): HasMany
    {
        return $this->hasMany(PhilippineCity::class, 'province_id');
    }
}
