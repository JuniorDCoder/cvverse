<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureOnboardingCompleted
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        $user = $request->user();

        if ($user && ! $user->hasCompletedOnboarding()) {
            // Allow access to onboarding routes and logout
            if ($request->routeIs('onboarding.*') || $request->routeIs('logout') || $request->routeIs('verification.*')) {
                return $next($request);
            }

            return redirect()->route('onboarding.welcome');
        }

        return $next($request);
    }
}
