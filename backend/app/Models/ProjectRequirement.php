<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ProjectRequirement extends Model
{
    use HasFactory;

    public const TYPE_MATERIAL = 'material';
    public const TYPE_TASK = 'task';

    protected $fillable = [
        'project_id',
        'type',
        'name',
        'quantity',
        'notes',
        'is_done',
        'completed_by',
        'completed_at',
        'sort_order',
    ];

    protected $casts = [
        'is_done' => 'boolean',
        'completed_at' => 'datetime',
    ];

    public function project(): BelongsTo
    {
        return $this->belongsTo(Project::class);
    }

    public function completedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'completed_by');
    }
}
