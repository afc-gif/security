<?php

namespace App\Models;

use Illuminate\Auth\Authenticatable;
use Illuminate\Contracts\Auth\Authenticatable as AuthenticatableContract;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Notifications\Notifiable;

class User extends Model implements AuthenticatableContract
{
    use Authenticatable, HasFactory, Notifiable;

    protected $fillable = [
        'name',
        'email',
        'password',
        'role',
        'status',
    ];

    protected $hidden = [
        'password',
    ];

    protected $casts = [
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    protected static function booted(): void
    {
        static::creating(function (User $user) {
            if (empty($user->role)) {
                $user->role = 'user';
            }
            if (empty($user->status)) {
                $user->status = 'approved';
            }
        });
    }

    public function orders()
    {
        return $this->hasMany(Order::class);
    }

    public function adminNotifications()
    {
        return $this->hasMany(AdminNotification::class);
    }

    public function financePermissions()
    {
        return $this->belongsToMany(FinancePermission::class, 'user_finance_permissions')
            ->withPivot(['granted_by', 'granted_at'])
            ->withTimestamps();
    }

    public function isAdmin()
    {
        return in_array($this->role, ['admin', 'manager', 'executive', 'super_admin'], true);
    }

    public function isExecutive(): bool
    {
        return in_array($this->role, ['executive', 'super_admin'], true);
    }

    public function isSuperAdmin(): bool
    {
        return $this->role === 'super_admin';
    }

    public function isPos()
    {
        return $this->role === 'pos';
    }

    public function isUser()
    {
        return $this->role === 'user';
    }

    public function isManager()
    {
        return $this->role === 'manager';
    }

    public function isFinance()
    {
        return $this->role === 'finance';
    }

    public function isFieldStaff()
    {
        return $this->role === 'field_staff';
    }

    public function isFieldCoordinator()
    {
        return $this->role === 'field_coordinator';
    }

    public function hasRole(array|string $roles)
    {
        $roles = is_array($roles) ? $roles : func_get_args();

        if (in_array($this->role, ['executive', 'super_admin'], true) && in_array('admin', $roles, true)) {
            return true;
        }

        return in_array($this->role, $roles, true);
    }

    public function hasFinancePermission(string $permission): bool
    {
        return $this->isFinance() || $this->isExecutive() || $this->financePermissions()->where('slug', $permission)->exists();
    }

    public function isPending()
    {
        return $this->status === 'pending';
    }

    public function isApproved()
    {
        return $this->status === 'approved';
    }

}
