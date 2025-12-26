<?php

namespace App\Http\Controllers\Admin;

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
        return view('Admin.auth.login');
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

            // Kiểm tra role
            if (Auth::user()->role !== 'admin') {
                Auth::logout();
                return back()->withErrors([
                    'email' => '❌ Bạn không có quyền truy cập hệ thống'
                ]);
            }

            return redirect()->route('admin.dashboard.courts');
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
        return redirect()->route('admin.login');
    }
}
