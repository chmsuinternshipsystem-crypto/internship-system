<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class DocumentWorkflowStep extends Model
{
    use HasFactory;

    protected $fillable = [
        'workflow_template_id',
        'step_order',
        'role',
        'can_return',
        'requires_signature',
    ];

    public function workflowTemplate()
    {
        return $this->belongsTo(DocumentWorkflowTemplate::class, 'workflow_template_id');
    }
}
