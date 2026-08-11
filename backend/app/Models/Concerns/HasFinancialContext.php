<?php

namespace App\Models\Concerns;

use App\Models\Inspection;
use App\Models\JobRequestItem;
use App\Models\Project;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphTo;

trait HasFinancialContext
{
    public function project(): BelongsTo
    {
        return $this->belongsTo(Project::class);
    }

    public function inspection(): BelongsTo
    {
        return $this->belongsTo(Inspection::class);
    }

    public function jobRequestItem(): BelongsTo
    {
        return $this->belongsTo(JobRequestItem::class);
    }

    public function originalContext(): MorphTo
    {
        return $this->morphTo(__FUNCTION__, 'original_context_type', 'original_context_id');
    }

    public function convertedProject(): ?Project
    {
        if ($this->project) {
            return $this->project;
        }

        if ($this->inspection) {
            return $this->inspection->project;
        }

        if ($this->jobRequestItem) {
            return $this->jobRequestItem->project;
        }

        return null;
    }
}
