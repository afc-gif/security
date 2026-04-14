<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Project extends Model
{
    use HasFactory;

    protected $fillable = [
        'project_code',
        'inspection_id',
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
        'created_by',
    ];

    protected $casts = [
        'start_date' => 'date',
        'deadline' => 'date',
    ];

    public function client()
    {
        return $this->belongsTo(Client::class);
    }

    public function inspection()
    {
        return $this->belongsTo(Inspection::class);
    }

    public function manager()
    {
        return $this->belongsTo(User::class, 'assigned_manager_id');
    }

    public function fieldStaff()
    {
        return $this->belongsTo(User::class, 'assigned_field_staff_id');
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
}
