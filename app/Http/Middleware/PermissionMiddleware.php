<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class PermissionMiddleware
{
    public function handle(Request $request, Closure $next, string $permission): Response
    {
        if (! $request->user()) {
            abort(403, 'Unauthorized action.');
        }

        // Support OR permissions: "perm-a|perm-b|perm-c"
        $permissions = array_values(array_filter(array_map('trim', explode('|', $permission))));
        $allowed = false;

        foreach ($permissions as $perm) {
            if ($request->user()->hasPermission($perm)) {
                $allowed = true;
                break;
            }
        }

        if (! $allowed) {
            abort(403, 'Unauthorized action.');
        }

        return $next($request);
    }
}
