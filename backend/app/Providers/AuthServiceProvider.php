<?php

namespace App\Providers;

use App\Models\FinancePermission;
use App\Models\User;
use Illuminate\Foundation\Support\Providers\AuthServiceProvider as ServiceProvider;
use Illuminate\Support\Facades\Gate;

class AuthServiceProvider extends ServiceProvider
{
    protected $policies = [
        // 'App\Models\Model' => 'App\Policies\ModelPolicy',
    ];

    public function boot()
    {
        $this->registerPolicies();

        foreach ([
            FinancePermission::VIEW,
            FinancePermission::CREATE,
            FinancePermission::EDIT,
            FinancePermission::APPROVE,
            FinancePermission::DELETE,
            FinancePermission::REPORTS,
        ] as $permission) {
            Gate::define($permission, fn (User $user) => $user->hasFinancePermission($permission));
        }
    }
}
