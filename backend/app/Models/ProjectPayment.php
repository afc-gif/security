<?php

namespace App\Models;

use App\Models\Concerns\HasFinancialContext;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphMany;

class ProjectPayment extends Model
{
    use HasFactory;
    use HasFinancialContext;

    public const TYPE_DEPOSIT = 'deposit';
    public const TYPE_PART_PAYMENT = 'part_payment';
    public const TYPE_FULL_PAYMENT = 'full_payment';
    public const TYPE_ADVANCE = 'advance';
    public const TYPE_OTHER = 'other';

    protected $fillable = [
        'project_id',
        'inspection_id',
        'job_request_id',
        'job_request_item_id',
        'client_id',
        'payment_type',
        'original_context_type',
        'original_context_id',
        'amount',
        'payment_date',
        'payment_method',
        'reference',
        'notes',
        'recorded_by',
        'created_by',
        'updated_by',
    ];

    protected $casts = [
        'amount' => 'decimal:2',
        'payment_date' => 'date',
    ];

    public function client(): BelongsTo
    {
        return $this->belongsTo(Client::class);
    }

    public function inspection(): BelongsTo
    {
        return $this->belongsTo(Inspection::class);
    }

    public function jobRequest(): BelongsTo
    {
        return $this->belongsTo(JobRequest::class);
    }

    public function jobRequestItem(): BelongsTo
    {
        return $this->belongsTo(JobRequestItem::class);
    }

    public function recorder(): BelongsTo
    {
        return $this->belongsTo(User::class, 'recorded_by');
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
