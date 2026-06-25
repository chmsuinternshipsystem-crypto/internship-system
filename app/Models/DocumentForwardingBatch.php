<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class DocumentForwardingBatch extends Model
{
    use HasFactory;

    protected $fillable = [
        'created_by',
        'release_at',
        'status',
        'notes',
    ];

    protected function casts(): array
    {
        return [
            'release_at' => 'datetime',
        ];
    }

    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function items()
    {
        return $this->hasMany(DocumentForwardingItem::class, 'batch_id');
    }

    public function logs()
    {
        return $this->hasMany(TransmittalLog::class, 'batch_id');
    }
}
