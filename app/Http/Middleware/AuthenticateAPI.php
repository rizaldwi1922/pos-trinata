<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class AuthenticateAPI
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        // Allow request from localhost
        if (env('APP_DEBUG')) {
            if ($request->getHost() == 'localhost' || strpos($request->getHost(), '127.0.0.1') !== false) {
                return $next($request);
            }
        }

        // Check for TOKEN header
        if (!$request->hasHeader('TOKEN') || $request->header('TOKEN') != env('APP_TOKEN')) {
            return response()->json([
                'status' => 'error',
                'message' => 'Unauthorized'
            ], 401);
        }

        return $next($request);
    }
}
