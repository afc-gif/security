<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use App\Models\User;

class AdminMiddleware
{
    public function handle(Request $request, Closure $next)
    {
        /** @var User|null $user */
        $user = auth()->user();
        
        if (!auth()->check() || !$user || !$user->isAdmin()) {
            abort(403, 'Unauthorized access');
        }

        return $next($request);
    }
}
