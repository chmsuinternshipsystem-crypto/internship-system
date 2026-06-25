<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Builder;

class CompanyIndustry extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'slug',
        'color',
        'is_active',
    ];

    public function companies()
    {
        return $this->hasMany(Company::class);
    }

    public function scopeActive(Builder $query): void
    {
        $query->where('is_active', true);
    }

    public function scopeOrdered(Builder $query): void
    {
        $query->orderBy('name');
    }
}
