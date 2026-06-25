<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class DocumentWorkflowTemplate extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'code',
        'description',
        'is_active',
    ];

    public function steps()
    {
        return $this->hasMany(DocumentWorkflowStep::class, 'workflow_template_id')->orderBy('step_order');
    }

    public function requiredDocuments()
    {
        return $this->hasMany(RequiredDocument::class, 'workflow_template_id');
    }
}
