<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

class RoleMiddleware
{
    public function handle(Request $request, Closure $next, ...$roles)
    {
        $user = $request->user();
        $roles = collect($roles)
            ->flatMap(fn ($role) => explode(',', $role))
            ->map(fn ($role) => trim($role))
            ->filter()
            ->values()
            ->all();

        if (!$user || !$user->hasRole($roles)) {
            abort(403, 'Unauthorized access');
        }

        return $next($request);
    }
}
