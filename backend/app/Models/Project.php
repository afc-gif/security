<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Project extends Model
{
    use HasFactory;

    public const EDIT_LOCK_TIMEOUT_MINUTES = 30;

    protected $fillable = [
        'project_code',
        'inspection_id',
        'job_request_item_id',
        'client_id',
        'title',
        'location',
        'description',
        'status',
        'progress_percentage',
        'priority',
        'start_date',
        'deadline',
        'assigned_manager_id',
        'assigned_field_staff_id',
        'active_editor_id',
        'editing_started_at',
        'created_by',
    ];

    protected $casts = [
        'start_date' => 'date',
        'deadline' => 'date',
        'editing_started_at' => 'datetime',
    ];

    public function client()
    {
        return $this->belongsTo(Client::class);
    }

    public function inspection()
    {
        return $this->belongsTo(Inspection::class);
    }

    public function jobRequestItem()
    {
        return $this->belongsTo(JobRequestItem::class);
    }

    public function manager()
    {
        return $this->belongsTo(User::class, 'assigned_manager_id');
    }

    public function fieldStaff()
    {
        return $this->belongsTo(User::class, 'assigned_field_staff_id');
    }

    public function activeEditor(): BelongsTo
    {
        return $this->belongsTo(User::class, 'active_editor_id');
    }

    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function updates()
    {
        return $this->hasMany(ProjectUpdate::class);
    }

    public function media()
    {
        return $this->hasMany(ProjectMedia::class);
    }

    public function tasks()
    {
        return $this->morphMany(Task::class, 'assignable');
    }

    public function requirements()
    {
        return $this->hasMany(ProjectRequirement::class)->orderBy('sort_order')->orderBy('id');
    }

    public function isBeingEdited(): bool
    {
        return $this->active_editor_id !== null && !$this->editingLockExpired();
    }

    public function editingLockExpired(): bool
    {
        return $this->active_editor_id !== null
            && $this->editing_started_at !== null
            && $this->editing_started_at->lt(now()->subMinutes(self::EDIT_LOCK_TIMEOUT_MINUTES));
    }

    public function canBeUpdatedBy(User $user): bool
    {
        return (int) $this->active_editor_id === (int) $user->id;
    }
}
