<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Inspection extends Model
{
    use HasFactory;

    protected $fillable = [
        'inspection_code',
        'client_id',
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
        'created_by',
    ];

    protected $casts = [
        'scheduled_date' => 'datetime',
        'submitted_at' => 'datetime',
        'reviewed_at' => 'datetime',
    ];

    public function client()
    {
        return $this->belongsTo(Client::class);
    }

    public function assignedUser()
    {
        return $this->belongsTo(User::class, 'assigned_to');
    }

    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function reviewedBy()
    {
        return $this->belongsTo(User::class, 'reviewed_by');
    }

    public function media()
    {
        return $this->hasMany(InspectionMedia::class);
    }

    public function project()
    {
        return $this->hasOne(Project::class);
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
}
