<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class InspectionRevision extends Model
{
    use HasFactory;

    public const ACTION_SUBMITTED = 'submitted';
    public const ACTION_RETURNED = 'returned';
    public const ACTION_APPROVED = 'approved';
    public const ACTION_REJECTED = 'rejected';

    protected $fillable = [
        'inspection_id',
        'user_id',
        'action',
        'findings',
        'risks_identified',
        'recommendations',
        'return_reason',
        'admin_notes',
        'snapshot_data',
    ];

    protected $casts = [
        'snapshot_data' => 'array',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    public function inspection(): BelongsTo
    {
        return $this->belongsTo(Inspection::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
