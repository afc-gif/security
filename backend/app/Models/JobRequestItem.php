<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

class JobRequestItem extends Model
{
    use HasFactory;

    // Status Constants
    const STATUS_OPEN = 'open';
    const STATUS_PENDING_ASSIGNMENT = 'pending_assignment';
    const STATUS_CLAIMED = 'claimed';
    const STATUS_SUBMITTED = 'submitted';
    const STATUS_PENDING_ADMIN_REVIEW = 'pending_admin_review';
    const STATUS_APPROVED = 'approved';
    const STATUS_REJECTED = 'rejected';
    const STATUS_RETURNED = 'returned';
    const STATUS_REOPENED = 'reopened';
    const STATUS_OVERDUE = 'overdue';
    const STATUS_CLOSED = 'closed';

    // Priority Constants
    const PRIORITY_LOW = 'low';
    const PRIORITY_MEDIUM = 'medium';
    const PRIORITY_HIGH = 'high';

    protected $fillable = [
        'job_request_id',
        'service_category_id',
        'title',
        'description',
        'claimed_by',
        'claimed_at',
        'status',
        'due_date',
        'reopened_at',
        'priority',
        'submitted_at',
        'created_by',
    ];

    protected $casts = [
        'claimed_at' => 'datetime',
        'due_date' => 'datetime',
        'reopened_at' => 'datetime',
        'submitted_at' => 'datetime',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    /**
     * Scope: items that can be claimed by field staff.
     */
    public function scopeAvailable(Builder $query): Builder
    {
        return $query
            ->whereIn('status', [self::STATUS_OPEN, self::STATUS_REOPENED])
            ->whereNull('claimed_by')
            ->where(function (Builder $dateQuery) {
                $dateQuery->whereNull('due_date')
                    ->orWhere('due_date', '>=', now());
            });
    }

    /**
     * Determine if this item is past deadline and still actionable.
     */
    public function isOverdue(): bool
    {
        return $this->status === self::STATUS_OVERDUE || (
            $this->due_date !== null
            && now()->greaterThan($this->due_date)
            && in_array($this->status, [
                self::STATUS_OPEN,
                self::STATUS_CLAIMED,
                self::STATUS_RETURNED,
                self::STATUS_REOPENED,
            ], true)
        );
    }

    /**
     * Determine if this item can still be claimed.
     */
    public function isClaimable(): bool
    {
        return in_array($this->status, [self::STATUS_OPEN, self::STATUS_REOPENED], true)
            && $this->claimed_by === null
            && !$this->isOverdue();
    }

    /**
     * Determine if this item can still be submitted.
     */
    public function isSubmittable(): bool
    {
        return in_array($this->status, [self::STATUS_CLAIMED, self::STATUS_RETURNED], true)
            && !$this->isOverdue();
    }

    /**
     * Determine if this item can create a project.
     * Super Admins can directly convert unassigned/non-approved jobs into projects.
     */
    public function isConvertibleToProject(?User $user = null): bool
    {
        if ($this->project()->exists()) {
            return false;
        }

        $user = $user ?? auth()->user();
        if ($user?->isSuperAdmin()) {
            return true;
        }

        return $this->status === self::STATUS_APPROVED;
    }

    /**
     * Persist overdue status when the item is touched by field or admin flows.
     */
    public function markOverdueIfPast(): bool
    {
        if ($this->status !== self::STATUS_OVERDUE && $this->isOverdue()) {
            $this->forceFill(['status' => self::STATUS_OVERDUE])->save();

            return true;
        }

        return false;
    }

    /**
     * Get the job request for this item.
     */
    public function jobRequest(): BelongsTo
    {
        return $this->belongsTo(JobRequest::class);
    }

    /**
     * Get the service category for this item.
     */
    public function serviceCategory(): BelongsTo
    {
        return $this->belongsTo(ServiceCategory::class);
    }

    /**
     * Get the project created from this category item.
     */
    public function project(): HasOne
    {
        return $this->hasOne(Project::class);
    }

    /**
     * Get the user who claimed this item.
     */
    public function claimedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'claimed_by');
    }

    /**
     * Get the user who claimed this item.
     */
    public function claimer(): BelongsTo
    {
        return $this->belongsTo(User::class, 'claimed_by');
    }

    /**
     * Get the user who created this item.
     */
    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    /**
     * Get all attempts for this item.
     */
    public function attempts(): HasMany
    {
        return $this->hasMany(JobItemAttempt::class);
    }

    public function checklistItems(): HasMany
    {
        return $this->hasMany(JobChecklistItem::class)
            ->orderBy('sort_order')
            ->orderBy('id');
    }

    public function financialExpenses(): HasMany
    {
        return $this->hasMany(FinancialExpense::class);
    }

    public function financialMaterialCosts(): HasMany
    {
        return $this->hasMany(FinancialMaterialCost::class);
    }

    public function ensureChecklistFromCategory(): void
    {
        if (!$this->service_category_id || $this->checklistItems()->exists()) {
            return;
        }

        $templates = CategoryChecklistTemplate::query()
            ->where('service_category_id', $this->service_category_id)
            ->where('is_active', true)
            ->orderBy('sort_order')
            ->orderBy('id')
            ->get();

        foreach ($templates as $index => $template) {
            $this->checklistItems()->create([
                'category_checklist_template_id' => $template->id,
                'title' => $template->title,
                'description' => $template->description,
                'input_type' => $template->input_type,
                'options' => $template->options,
                'status' => JobChecklistItem::STATUS_PENDING,
                'is_required' => $template->is_required,
                'is_custom' => false,
                'sort_order' => $index,
            ]);
        }
    }

    /**
     * Get the most recent attempt.
     */
    public function latestAttempt()
    {
        return $this->hasOne(JobItemAttempt::class)->latest('created_at');
    }

    /**
     * Check if a user has a rejected attempt on this item (blocking logic).
     * Users with rejected attempts cannot reclaim this item.
     */
    public function hasUserBeenRejected(int $userId): bool
    {
        return $this->attempts()
            ->where('user_id', $userId)
            ->where('status', JobItemAttempt::STATUS_REJECTED)
            ->exists();
    }

    /**
     * Check if a user can claim this item.
     * They cannot claim if:
     * - Item is not in 'open' or 'reopened' status
     * - They previously failed (have a rejected attempt)
     */
    public function canBeClaimedBy(int $userId): bool
    {
        // Can only claim open or reopened items
        if (!in_array($this->status, [self::STATUS_OPEN, self::STATUS_REOPENED], true)) {
            return false;
        }

        if ($this->claimed_by !== null) {
            return false;
        }

        // Cannot reclaim if previously rejected
        return !$this->hasUserBeenRejected($userId);
    }

    /**
     * Get list of user IDs who failed this item (for UI blocking).
     */
    public function getRejectedUserIds()
    {
        return $this->attempts()
            ->where('status', JobItemAttempt::STATUS_REJECTED)
            ->distinct()
            ->pluck('user_id');
    }

    /**
     * Mark item as reopened by admin.
     */
    public function reopenByAdmin(?string $reason = null): void
    {
        $this->update([
            'status' => self::STATUS_REOPENED,
            'reopened_at' => now(),
            'claimed_by' => null,
            'claimed_at' => null,
        ]);

        // Optionally log the reopening reason
        if ($reason) {
            JobItemAttempt::create([
                'job_request_item_id' => $this->id,
                'user_id' => auth()->id() ?? 1,
                'status' => 'reopened',
                'notes' => "Reopened by admin: {$reason}",
            ]);
        }
    }

    public function payments()
    {
        return $this->hasMany(ProjectPayment::class, 'job_request_item_id');
    }
}
