<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;

class EventServiceProvider extends ServiceProvider
{
    protected $listen = [
        // Event::class => [
        //     Listener::class,
        // ],
    ];

    public function boot()
    {
        //
    }
}
