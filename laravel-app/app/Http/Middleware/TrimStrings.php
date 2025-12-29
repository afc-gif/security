<?php

namespace App\Http\Middleware;

class TrimStrings
{
    protected $except = [
        'current_password',
        'password',
        'password_confirmation',
    ];

    public function handle($request, $next)
    {
        return $next($request);
    }
}
