<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class JobItemMedia extends Model
{
    use HasFactory;

    protected $table = 'job_item_media';

    protected $fillable = [
        'job_item_attempt_id',
        'job_checklist_item_id',
        'uploaded_by',
        'file_path',
        'cloudinary_public_id',
        'cloudinary_resource_type',
        'file_name',
        'file_type',
        'file_size',
    ];

    public function attempt(): BelongsTo
    {
        return $this->belongsTo(JobItemAttempt::class, 'job_item_attempt_id');
    }

    public function checklistItem(): BelongsTo
    {
        return $this->belongsTo(JobChecklistItem::class, 'job_checklist_item_id');
    }

    public function uploader(): BelongsTo
    {
        return $this->belongsTo(User::class, 'uploaded_by');
    }
}
