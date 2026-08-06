<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class JobItemAttempt extends Model
{
    use HasFactory;

    // Status constants
    const STATUS_SUBMITTED = 'submitted';
    const STATUS_COORDINATOR_APPROVED = 'coordinator_approved';
    const STATUS_REJECTED = 'rejected';
    const STATUS_APPROVED = 'approved';
    const STATUS_RETURNED = 'returned';

    protected $fillable = [
        'job_request_item_id',
        'user_id',
        'status',
        'notes',
    ];

    protected $casts = [
        'created_at' => 'datetime',
    ];

    /**
     * Get the job request item for this attempt.
     */
    public function jobRequestItem(): BelongsTo
    {
        return $this->belongsTo(JobRequestItem::class);
    }

    /**
     * Get the user who made this attempt.
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function requirements(): HasMany
    {
        return $this->hasMany(JobItemRequirement::class)->orderBy('sort_order')->orderBy('id');
    }

    public function media(): HasMany
    {
        return $this->hasMany(JobItemMedia::class)->orderBy('id');
    }
}
