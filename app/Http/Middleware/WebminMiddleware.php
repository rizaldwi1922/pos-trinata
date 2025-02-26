<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class WebminMiddleware
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        if (env('APP_WEBMIN', false) == false) {
            if (auth()->user()) {
                return redirect()->route('admin.dashboard');
            } else {
                return redirect()->route('auth.login');
            }
        }

        return $next($request);
    }
}
