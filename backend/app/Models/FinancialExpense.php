<?php

namespace App\Models;

use App\Models\Concerns\HasFinancialContext;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphMany;

class FinancialExpense extends Model
{
    use HasFactory;
    use HasFinancialContext;

    public const STATUS_PENDING = 'pending';
    public const STATUS_APPROVED = 'approved';
    public const STATUS_REJECTED = 'rejected';

    protected $fillable = [
        'project_id',
        'inspection_id',
        'job_request_item_id',
        'original_context_type',
        'original_context_id',
        'finance_expense_category_id',
        'description',
        'amount',
        'incurred_on',
        'status',
        'submitted_by',
        'approved_by',
        'approved_at',
        'notes',
        'created_by',
        'updated_by',
        'is_office_expense',
        'payment_method',
        'reference',
    ];

    protected $casts = [
        'amount' => 'decimal:2',
        'incurred_on' => 'date',
        'approved_at' => 'datetime',
        'is_office_expense' => 'boolean',
    ];

    /**
     * Scope: only office / general company expenses.
     */
    public function scopeOffice(\Illuminate\Database\Eloquent\Builder $query): \Illuminate\Database\Eloquent\Builder
    {
        return $query->where('is_office_expense', true);
    }

    /**
     * Scope: only job/project/inspection-linked expenses (non-office).
     */
    public function scopeOperational(\Illuminate\Database\Eloquent\Builder $query): \Illuminate\Database\Eloquent\Builder
    {
        return $query->where('is_office_expense', false);
    }

    public function category(): BelongsTo
    {
        return $this->belongsTo(FinanceExpenseCategory::class, 'finance_expense_category_id');
    }

    public function submitter(): BelongsTo
    {
        return $this->belongsTo(User::class, 'submitted_by');
    }

    public function approver(): BelongsTo
    {
        return $this->belongsTo(User::class, 'approved_by');
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function updater(): BelongsTo
    {
        return $this->belongsTo(User::class, 'updated_by');
    }

    public function documents(): MorphMany
    {
        return $this->morphMany(FinancialDocument::class, 'documentable');
    }
}
