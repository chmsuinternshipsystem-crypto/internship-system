<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class StudentDocumentAction extends Model
{
    use HasFactory;

    protected $fillable = [
        'student_document_id',
        'actor_id',
        'actor_role',
        'action',
        'from_status',
        'to_status',
        'note',
        'metadata',
        'acted_at',
    ];

    protected function casts(): array
    {
        return [
            'metadata' => 'array',
            'acted_at' => 'datetime',
        ];
    }

    public function studentDocument()
    {
        return $this->belongsTo(StudentDocument::class);
    }

    public function actor()
    {
        return $this->belongsTo(User::class, 'actor_id');
    }
}
