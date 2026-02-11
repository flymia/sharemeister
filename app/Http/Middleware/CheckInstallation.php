<?php

namespace App\Http\Middleware;

use Closure;
use App\Models\User;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class CheckInstallation
{
    public function handle(Request $request, Closure $next): Response
    {
        // 1. Check if an admin exists
        $adminExists = User::where('is_admin', true)->exists();

        // 2. If no admin exists...
        if (!$adminExists) {
            // Allow access ONLY to the setup page itself
            // We check both the route name AND the URI to be safe
            if ($request->routeIs('setup.required') || $request->is('setup-required')) {
                return $next($request);
            }

            // Redirect everything else to the setup page
            return redirect()->route('setup.required');
        }

        // 3. If admin exists, but user tries to visit setup page, send them home
        if ($request->routeIs('setup.required') || $request->is('setup-required')) {
            return redirect()->route('landing');
        }

        return $next($request);
    }
}