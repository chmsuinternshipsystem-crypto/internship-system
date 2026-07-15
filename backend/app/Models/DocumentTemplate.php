<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class DocumentTemplate extends Model
{
    protected $fillable = [
        'required_document_id',
        'file_path',
        'original_name',
        'uploaded_by',
    ];

    public function requiredDocument(): BelongsTo
    {
        return $this->belongsTo(RequiredDocument::class);
    }

    public function uploader(): BelongsTo
    {
        return $this->belongsTo(User::class, 'uploaded_by');
    }
}
