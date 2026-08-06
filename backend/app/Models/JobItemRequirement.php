<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class JobItemRequirement extends Model
{
    use HasFactory;

    public const TYPE_MATERIAL = 'material';
    public const TYPE_TASK = 'task';

    protected $fillable = [
        'job_item_attempt_id',
        'type',
        'name',
        'quantity',
        'notes',
        'sort_order',
    ];

    public function attempt(): BelongsTo
    {
        return $this->belongsTo(JobItemAttempt::class, 'job_item_attempt_id');
    }
}
