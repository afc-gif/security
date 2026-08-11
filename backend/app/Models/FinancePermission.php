<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class FinancePermission extends Model
{
    use HasFactory;

    public const VIEW = 'finance.view';
    public const CREATE = 'finance.create';
    public const EDIT = 'finance.edit';
    public const APPROVE = 'finance.approve';
    public const DELETE = 'finance.delete';
    public const REPORTS = 'finance.reports';

    protected $fillable = [
        'slug',
        'name',
        'description',
    ];

    public function users(): BelongsToMany
    {
        return $this->belongsToMany(User::class, 'user_finance_permissions')
            ->withPivot(['granted_by', 'granted_at'])
            ->withTimestamps();
    }
}
