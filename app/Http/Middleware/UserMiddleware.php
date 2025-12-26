<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Support\Facades\Auth;
use Illuminate\Http\Response;

class UserMiddleware
{
    public function handle($request, Closure $next)
    {
        // Kiểm tra đăng nhập
        if (!Auth::check()) {
            if ($request->expectsJson()) {
                return response()->json([
                    'message' => 'Unauthorized'
                ], Response::HTTP_UNAUTHORIZED);
            }
            return redirect()->route('login');
        }

        $user = Auth::user();

        // Kiểm tra role user
        if (!$user->isUser()) {
            if ($request->expectsJson()) {
                return response()->json([
                    'message' => 'Access denied'
                ], Response::HTTP_FORBIDDEN);
            }
            return redirect()->route('admin.dashboard');
        }

        return $next($request);
    }
}
