<?php

namespace App\Providers;

use App\Models\AdminNotification;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\View;

class AppServiceProvider extends ServiceProvider
{
    public function register()
    {
        //
    }

    public function boot()
    {
        View::composer('admin.layout', function ($view) {
            $notifications = collect();

            try {
                if (auth()->check() && auth()->user()?->isAdmin() && Schema::hasTable('admin_notifications')) {
                    $notifications = AdminNotification::query()
                        ->where('user_id', auth()->id())
                        ->whereNull('read_at')
                        ->latest('id')
                        ->limit(3)
                        ->get();
                }
            } catch (\Throwable $exception) {
                $notifications = collect();
            }

            $view->with('adminUnreadNotifications', $notifications);
        });
    }
}
