<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Storage;

class StudentDocument extends Model
{
    use HasFactory;

    protected $fillable = [
        'student_id',
        'required_document_id',
        'status',
        'file_path',
        'submitted_at',
        'verified_by',
        'uploaded_by',
        'workflow_template_id',
        'current_step_order',
        'current_holder_role',
        'next_step_role',
        'workflow_status',
        'last_action_at',
    ];

    protected function casts(): array
    {
        return [
            'submitted_at' => 'datetime',
            'last_action_at' => 'datetime',
        ];
    }

    public function student()
    {
        return $this->belongsTo(Student::class);
    }

    public function requiredDocument()
    {
        return $this->belongsTo(RequiredDocument::class);
    }

    public function verifier()
    {
        return $this->belongsTo(User::class, 'verified_by');
    }

    public function workflowTemplate()
    {
        return $this->belongsTo(DocumentWorkflowTemplate::class, 'workflow_template_id');
    }

    public function actions()
    {
        return $this->hasMany(StudentDocumentAction::class)->orderByDesc('acted_at');
    }

    protected static function booted(): void
    {
        static::deleting(function (StudentDocument $doc): void {
            if ($doc->file_path) {
                Storage::disk('public')->delete($doc->file_path);
            }
        });
    }
}
