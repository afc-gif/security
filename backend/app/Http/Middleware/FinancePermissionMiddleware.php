<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

class FinancePermissionMiddleware
{
    public function handle(Request $request, Closure $next, string $permission = 'finance.view')
    {
        $user = $request->user();

        if (!$user || !$user->hasFinancePermission($permission)) {
            abort(403, 'Unauthorized financial access');
        }

        return $next($request);
    }
}
