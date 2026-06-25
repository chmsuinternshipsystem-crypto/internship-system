<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class TransmittalLog extends Model
{
    use HasFactory;

    protected $fillable = [
        'batch_id',
        'item_id',
        'student_id',
        'required_document_id',
        'action_type',
        'acted_by',
        'acted_at',
        'note',
    ];

    protected function casts(): array
    {
        return [
            'acted_at' => 'datetime',
        ];
    }

    public function actor()
    {
        return $this->belongsTo(User::class, 'acted_by');
    }
}
