<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class AuthController extends Controller
{
    /**
     * Form đăng nhập
     */
    public function showLoginForm()
    {
        return view('auth.login');
    }

    /**
     * Xử lý đăng nhập
     */
    public function login(Request $request)
    {
        $request->validate([
            'email' => 'required|email',
            'password' => 'required',
        ]);

        if (Auth::attempt($request->only('email', 'password'))) {
            $user = Auth::user();

            // Redirect based on role
            if ($user->isAdmin()) {
                return redirect()->route('admin.dashboard.courts');
            } elseif ($user->isUser()) {
                return redirect()->route('user.dashboard');
            }

            // If role not recognized, logout
            Auth::logout();
            return back()->withErrors([
                'email' => '❌ Bạn không có quyền truy cập hệ thống'
            ]);
        }

        return back()->withErrors([
            'email' => '❌ Email hoặc mật khẩu không đúng'
        ]);
    }

    /**
     * Logout
     */
    public function logout()
    {
        Auth::logout();
        return redirect()->route('login');
    }
}
