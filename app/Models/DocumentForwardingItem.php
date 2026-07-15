<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class DocumentForwardingItem extends Model
{
    use HasFactory;

    protected $fillable = [
        'batch_id',
        'student_document_id',
        'student_id',
        'required_document_id',
        'released_at',
        'acknowledged_by',
        'acknowledged_at',
    ];

    protected function casts(): array
    {
        return [
            'released_at' => 'datetime',
            'acknowledged_at' => 'datetime',
        ];
    }

    public function batch()
    {
        return $this->belongsTo(DocumentForwardingBatch::class, 'batch_id');
    }

    public function studentDocument()
    {
        return $this->belongsTo(StudentDocument::class, 'student_document_id');
    }

    public function student()
    {
        return $this->belongsTo(Student::class);
    }

    public function requiredDocument()
    {
        return $this->belongsTo(RequiredDocument::class);
    }
}
