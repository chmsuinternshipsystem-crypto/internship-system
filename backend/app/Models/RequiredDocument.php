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

    private const MANDATORY_IDS_CACHE_KEY = 'required_documents_mandatory_ids';
    private const ORDERED_CACHE_KEY = 'required_documents_ordered';

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

    public function template()
    {
        return $this->hasOne(\App\Models\DocumentTemplate::class);
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

    public static function cachedMandatoryIds(): \Illuminate\Support\Collection
    {
        return cache()->rememberForever(self::MANDATORY_IDS_CACHE_KEY, function () {
            return static::where('is_mandatory', true)->pluck('id');
        });
    }

    public static function cachedOrdered(): \Illuminate\Database\Eloquent\Collection
    {
        return cache()->rememberForever(self::ORDERED_CACHE_KEY, function () {
            return static::orderBy('order_index')->orderBy('name')->get();
        });
    }

    public static function flushCache(): void
    {
        cache()->forget(self::MANDATORY_IDS_CACHE_KEY);
        cache()->forget(self::ORDERED_CACHE_KEY);
    }
}
