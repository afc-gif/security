<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class JobChecklistItem extends Model
{
    use HasFactory;

    public const STATUS_PENDING = 'pending';
    public const STATUS_DONE = 'done';
    public const STATUS_NOT_APPLICABLE = 'not_applicable';

    protected $fillable = [
        'job_request_item_id',
        'inspection_id',
        'category_checklist_template_id',
        'added_by',
        'completed_by',
        'title',
        'description',
        'input_type',
        'options',
        'response',
        'status',
        'notes',
        'is_required',
        'is_custom',
        'sort_order',
        'completed_at',
    ];

    protected $casts = [
        'is_required' => 'boolean',
        'is_custom' => 'boolean',
        'completed_at' => 'datetime',
        'options' => 'array',
    ];

    public function jobRequestItem(): BelongsTo
    {
        return $this->belongsTo(JobRequestItem::class);
    }

    public function inspection(): BelongsTo
    {
        return $this->belongsTo(Inspection::class);
    }

    public function template(): BelongsTo
    {
        return $this->belongsTo(CategoryChecklistTemplate::class, 'category_checklist_template_id');
    }

    public function addedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'added_by');
    }

    public function completedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'completed_by');
    }

    public function media(): HasMany
    {
        return $this->hasMany(JobItemMedia::class)->orderBy('id');
    }
}
