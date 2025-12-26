<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rule;

class UserController extends Controller
{
    /**
     * Danh sách người dùng
     */
    public function index()
    {
        $users = User::latest()->get();

        return view('Admin.users.index', compact('users'));
    }

    /**
     * Trang tạo người dùng mới
     */
    public function create()
    {
        return view('Admin.users.create');
    }

    /**
     * Lưu người dùng mới
     */
    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:users',
            'password' => 'required|string|min:8|confirmed',
            'role' => 'required|in:admin,customer',
            'phone' => 'nullable|string|max:20',
        ]);

        User::create([
            'name' => $request->name,
            'email' => $request->email,
            'password' => Hash::make($request->password),
            'role' => $request->role,
            'phone' => $request->phone,
        ]);

        return redirect()->route('admin.users.index')
            ->with('success', '✅ Tạo người dùng thành công');
    }

    /**
     * Trang chỉnh sửa người dùng
     */
    public function edit(User $user)
    {
        return view('Admin.users.edit', compact('user'));
    }

    /**
     * Cập nhật người dùng
     */
    public function update(Request $request, User $user)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'email' => ['required', 'string', 'email', 'max:255', Rule::unique('users')->ignore($user->id)],
            'password' => 'nullable|string|min:8|confirmed',
            'role' => 'required|in:admin,customer',
            'phone' => 'nullable|string|max:20',
        ]);

        $data = [
            'name' => $request->name,
            'email' => $request->email,
            'role' => $request->role,
            'phone' => $request->phone,
        ];

        if ($request->filled('password')) {
            $data['password'] = Hash::make($request->password);
        }

        $user->update($data);

        return redirect()->route('admin.users.index')
            ->with('success', '✅ Cập nhật người dùng thành công');
    }

    /**
     * Trang xác nhận xóa người dùng
     */
    public function delete(User $user)
    {
        return view('Admin.users.delete', compact('user'));
    }

    /**
     * Xóa người dùng
     */
    public function destroy(User $user)
    {
        // Prevent deleting the last admin or self
        if ($user->isAdmin() && User::where('role', 'admin')->count() <= 1) {
            return redirect()->route('admin.users.index')
                ->with('error', '❌ Không thể xóa admin cuối cùng');
        }

        if ($user->id === auth()->id()) {
            return redirect()->route('admin.users.index')
                ->with('error', '❌ Không thể xóa tài khoản của chính mình');
        }

        $user->delete();

        return redirect()->route('admin.users.index')
            ->with('success', '✅ Xóa người dùng thành công');
    }
}
