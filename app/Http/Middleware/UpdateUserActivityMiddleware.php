<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;
use Auth;
use Carbon\Carbon;
class UpdateUserActivityMiddleware
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle($request, Closure $next)
    {
        if (Auth::check()) {
            $user = Auth::user();
            $lastLogin = $user->last_login;
            $user->last_login = now();
            $user->save();

            if ($lastLogin) {
                $duration = $lastLogin->diffInSeconds(now());
                $user->total_online_hours += $duration / 3600; // Convert seconds to hours
                $user->save();
            }
        }

        return $next($request);
    }
}
