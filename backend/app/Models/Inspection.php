<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Inspection extends Model
{
    use HasFactory;

    public const STATUS_PENDING = 'pending';
    public const STATUS_ASSIGNED = 'assigned';
    public const STATUS_COMPLETED = 'completed';
    public const STATUS_RETURNED = 'returned';

    public const REVIEW_STATUS_PENDING = 'pending_review';
    public const REVIEW_STATUS_APPROVED = 'approved';
    public const REVIEW_STATUS_REJECTED = 'rejected';
    public const REVIEW_STATUS_RETURNED = 'returned';

    protected $fillable = [
        'inspection_code',
        'client_id',
        'service_category_id',
        'job_request_item_id',
        'title',
        'location',
        'inspection_type',
        'scheduled_date',
        'assigned_to',
        'status',
        'priority',
        'findings',
        'risks_identified',
        'recommendations',
        'submitted_at',
        'review_status',
        'reviewed_by',
        'reviewed_at',
        'review_notes',
        'return_reason',
        'returned_at',
        'returned_by',
        'created_by',
    ];

    protected $casts = [
        'scheduled_date' => 'datetime',
        'submitted_at' => 'datetime',
        'reviewed_at' => 'datetime',
        'returned_at' => 'datetime',
    ];

    public function client(): BelongsTo
    {
        return $this->belongsTo(Client::class);
    }

    public function serviceCategory(): BelongsTo
    {
        return $this->belongsTo(ServiceCategory::class);
    }

    public function jobRequestItem(): BelongsTo
    {
        return $this->belongsTo(JobRequestItem::class);
    }

    public function assignedUser(): BelongsTo
    {
        return $this->belongsTo(User::class, 'assigned_to');
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function reviewedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'reviewed_by');
    }

    public function returnedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'returned_by');
    }

    public function media(): HasMany
    {
        return $this->hasMany(InspectionMedia::class);
    }

    public function project()
    {
        return $this->hasOne(Project::class);
    }

    public function checklistItems(): HasMany
    {
        return $this->hasMany(JobChecklistItem::class, 'inspection_id')->orderBy('sort_order')->orderBy('id');
    }

    public function revisions(): HasMany
    {
        return $this->hasMany(InspectionRevision::class)->latest('id');
    }

    public function tasks()
    {
        return $this->morphMany(Task::class, 'assignable');
    }

    public function financialExpenses()
    {
        return $this->hasMany(FinancialExpense::class);
    }

    public function financialMaterialCosts()
    {
        return $this->hasMany(FinancialMaterialCost::class);
    }

    public function payments()
    {
        return $this->hasMany(ProjectPayment::class, 'inspection_id');
    }

    public function quotations()
    {
        return $this->hasMany(Quotation::class, 'inspection_id');
    }

    public function getEffectiveChecklistItemsAttribute()
    {
        if ($this->checklistItems->count() > 0) {
            return $this->checklistItems;
        }

        if ($this->jobRequestItem && $this->jobRequestItem->checklistItems->count() > 0) {
            return $this->jobRequestItem->checklistItems;
        }

        return collect();
    }

    public function ensureChecklistFromCategory(): void
    {
        if ($this->checklistItems()->exists()) {
            return;
        }

        $categoryId = $this->service_category_id ?? $this->jobRequestItem?->service_category_id;
        
        if (!$categoryId && $this->inspection_type) {
            $category = ServiceCategory::where('name', 'like', "%{$this->inspection_type}%")->first();
            $categoryId = $category?->id;
        }

        if (!$categoryId) {
            return;
        }

        $templates = CategoryChecklistTemplate::query()
            ->where('service_category_id', $categoryId)
            ->where('is_active', true)
            ->orderBy('sort_order')
            ->orderBy('id')
            ->get();

        foreach ($templates as $template) {
            $this->checklistItems()->create([
                'category_checklist_template_id' => $template->id,
                'title' => $template->title,
                'description' => $template->description,
                'input_type' => $template->input_type ?? 'textarea',
                'options' => $template->options,
                'status' => JobChecklistItem::STATUS_PENDING,
                'is_required' => $template->is_required,
                'is_custom' => false,
                'sort_order' => $template->sort_order,
                'added_by' => $this->created_by,
            ]);
        }
    }
}
