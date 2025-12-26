<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Support\Facades\Auth;
use Illuminate\Http\Response;

class AdminMiddleware
{
    public function handle($request, Closure $next)
{
    if (!Auth::check() || Auth::user()->role !== 'admin') {

        // Nếu là API → trả JSON
        if ($request->expectsJson()) {
            return response()->json([
                'message' => 'Unauthorized'
            ], Response::HTTP_UNAUTHORIZED);
        }

        // Nếu là web
        return redirect()->route('login');
    }

    return $next($request);
}
}
