<?php

namespace App\Http\Middleware;

use App\Models\UserActivity;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

class TrackUserActivity
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        // Skip tracking for admin routes - only track frontend visitors
        if ($request->is('admin/*') || $request->is('admin')) {
            return $next($request);
        }

        // Track activity for web requests (not API)
        if ($request->hasSession()) {
            $sessionId = $request->session()->getId();

            // Only update every 60 seconds to reduce database load
            $cacheKey = 'user_activity_' . $sessionId;
            if (!cache()->has($cacheKey)) {
                UserActivity::updateOrCreate(
                    ['session_id' => $sessionId],
                    [
                        'user_id' => Auth::id(),
                        'ip_address' => $request->ip(),
                        'last_activity_at' => now(),
                    ]
                );
                cache()->put($cacheKey, true, 60); // Cache for 60 seconds
            }
        }

        return $next($request);
    }
}
