<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Builder;

class JobRequest extends Model
{
    use HasFactory;

    protected $fillable = [
        'client_id',
        'title',
        'description',
        'created_by',
        'status',
    ];

    protected $casts = [
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    /**
     * Get the client for this job request.
     */
    public function client(): BelongsTo
    {
        return $this->belongsTo(Client::class);
    }

    /**
     * Get the user who created this job request.
     */
    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    /**
     * Get all job request items for this job request.
     */
    public function jobRequestItems(): HasMany
    {
        return $this->hasMany(JobRequestItem::class);
    }

    /**
     * Scope: Get available items for a specific user (not rejected by that user).
     * Excludes items where the user has a rejected attempt.
     */
    public function scopeAvailableFor(Builder $query, int $userId)
    {
        return $query->whereDoesntHave('jobRequestItems.attempts', function (Builder $attemptQuery) use ($userId) {
            $attemptQuery->where('user_id', $userId)
                ->where('status', JobItemAttempt::STATUS_REJECTED);
        });
    }
}
