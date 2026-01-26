<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class RedirectIfAdmin
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        $user = $request->user();

        // If user is admin and trying to access dashboard, redirect to admin dashboard
        if ($user && $user->isAdmin() && $request->routeIs('dashboard')) {
            return redirect()->route('admin.dashboard');
        }

        return $next($request);
    }
}
