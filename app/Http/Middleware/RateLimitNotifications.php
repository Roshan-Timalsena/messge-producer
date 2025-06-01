<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\RateLimiter;
use Symfony\Component\HttpFoundation\Response;

class RateLimitNotifications
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next)
    {
        $key = 'user_' . $request->input('user_id');
        $maxAttempts = 10;

        if (RateLimiter::tooManyAttempts($key, $maxAttempts)) {
            $retryAfter = RateLimiter::availableIn($key);
            return response()->json([
                'message' => 'Too many requests',
                'retry_after' => $retryAfter
            ], 429);
        }

        RateLimiter::hit($key, 3600);
        return $next($request);
    }
}
