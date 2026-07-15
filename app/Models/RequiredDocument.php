<?php

namespace App\Models;

use App\Support\HasDeleteProtection;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class RequiredDocument extends Model
{
    /** @use HasDeleteProtection<RequiredDocument> */
    use HasDeleteProtection;

    use HasFactory;

    protected $fillable = [
        'name',
        'description',
        'is_mandatory',
        'order_index',
        'workflow_template_id',
        'company_id',
        'submission_mode',
        'submission_deadline_at',
        'phase',
    ];

    public function deleteBlockers(): array
    {
        $count = $this->studentDocuments()->count();
        if ($count > 0) {
            return [__('Cannot delete: ":name" has :count student submission(s).', [
                'name' => $this->name,
                'count' => $count,
            ])];
        }

        return [];
    }

    protected function casts(): array
    {
        return [
            'submission_deadline_at' => 'datetime',
        ];
    }

    public function studentDocuments()
    {
        return $this->hasMany(StudentDocument::class);
    }

    public function workflowTemplate()
    {
        return $this->belongsTo(DocumentWorkflowTemplate::class, 'workflow_template_id');
    }

    public function company()
    {
        return $this->belongsTo(Company::class);
    }
}
