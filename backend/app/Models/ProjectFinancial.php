<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ProjectFinancial extends Model
{
    use HasFactory;

    protected $fillable = [
        'project_id',
        'contract_value',
        'approved_budget',
        'financial_notes',
        'created_by',
        'updated_by',
    ];

    protected $casts = [
        'contract_value' => 'decimal:2',
        'approved_budget' => 'decimal:2',
    ];

    public function project(): BelongsTo
    {
        return $this->belongsTo(Project::class);
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function updater(): BelongsTo
    {
        return $this->belongsTo(User::class, 'updated_by');
    }
}
