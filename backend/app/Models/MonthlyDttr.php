<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Facades\Storage;

class MonthlyDttr extends Model
{
    protected $table = 'monthly_dttrs';

    protected $fillable = [
        'student_id',
        'deployment_id',
        'year',
        'month',
        'file_path',
        'file_name',
    ];

    public function student(): BelongsTo
    {
        return $this->belongsTo(Student::class);
    }

    public function deployment(): BelongsTo
    {
        return $this->belongsTo(Deployment::class);
    }

    protected static function booted(): void
    {
        static::deleting(function (MonthlyDttr $dttr): void {
            if ($dttr->file_path) {
                Storage::disk('public')->delete($dttr->file_path);
            }
        });
    }
}
