<?php

use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Auth;
use App\Http\Controllers\Admin\{
    CourtController,
    BookingController,
    PaymentController,
    DashboardController,
    UserController,
    AuthController as AdminAuthController,
    MonthlyRentalController
};
use App\Http\Controllers\UserController as FrontendUserController;
use App\Http\Controllers\AuthController;

Route::get('/', function () {
    return redirect()->route('dashboard.courts');
});

Route::get('/home', function () {
    if (Auth::check()) {
        $user = Auth::user();
        if ($user->isAdmin()) {
            return redirect()->route('admin.dashboard.courts');
        } else {
            return redirect()->route('user.dashboard');
        }
    }
    return redirect()->route('login');
});
// Authentication Routes for Guests
Route::middleware('guest')->group(function () {
    Route::get('/login', [AuthController::class, 'showLoginForm'])->name('login');
    Route::post('/login', [AuthController::class, 'login'])->name('login.submit');
});
    Route::post('/logout', [AuthController::class, 'logout'])->name('logout');
// Admin Routes
Route::prefix('admin')->name('admin.')->middleware('admin')->group(function () {
    Route::controller(AdminAuthController::class)->group(function () {
    Route::get('/login', 'showLoginForm')->name('login');
    Route::post('/login', 'login')->name('login.submit');
    Route::post('/logout', 'logout')->name('logout');
});

    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard.courts');
    Route::resource('courts', CourtController::class);
    Route::get('courts/{court}/delete', [CourtController::class, 'delete'])->name('courts.delete');
    Route::resource('bookings', BookingController::class);
    Route::get('bookings/{booking}/delete', [BookingController::class, 'delete'])->name('bookings.delete');
    Route::resource('payments', PaymentController::class)->except(['create']);
    Route::controller(PaymentController::class)->group(function () {
    Route::get('payments/create', 'create')->name('payments.create');
    Route::post('payments/manual', 'storeManual')->name('payments.storeManual');
    Route::get('payments/{booking}/pay', 'pay')->name('payments.pay');
    Route::post('payments/{booking}/confirm', 'confirmTransfer')->name('payments.confirm');
    });
// Dòng này định nghĩa toàn bộ phương thức CRUD cho tài nguyên "users" trong ứng dụng Laravel, tự động tạo các tuyến đường (routes) cần thiết để quản lý người dùng, bao gồm hiển thị danh sách người dùng, tạo mới, chỉnh sửa, cập nhật và xóa người dùng.
    Route::resource('users', UserController::class);
    Route::get('users/{user}/delete', [UserController::class, 'delete'])->name('users.delete');
    Route::resource('monthly-rentals', MonthlyRentalController::class)->only(['index','create','store'])->names('monthly-rentals');
});
// User Routes
Route::prefix('user')->name('user.')->middleware('user')->group(function () {
    // Dashboard
    Route::get('/dashboard', [FrontendUserController::class, 'dashboard'])->name('dashboard');
    // Profile
    Route::get('/profile', [FrontendUserController::class, 'profile'])->name('profile');
    Route::put('/profile', [FrontendUserController::class, 'updateProfile'])->name('profile.update');
    // Courts
    Route::get('/courts', [FrontendUserController::class, 'courts'])->name('courts');
    Route::get('/courts/timetable', [FrontendUserController::class, 'getTimetable'])->name('courts.timetable');
    Route::get('/courts/{id}', [FrontendUserController::class, 'showCourt'])->name('courts.show');
    Route::get('/courts/{courtId}/schedule', [FrontendUserController::class, 'getCourtSchedule'])->name('courts.schedule');
    // Map
    Route::get('/map', [FrontendUserController::class, 'map'])->name('map');
    // Bookings
    Route::get('/bookings', [FrontendUserController::class, 'bookings'])->name('bookings');
    Route::get('/courts/{courtId}/book', [FrontendUserController::class, 'createBooking'])->name('bookings.create');
    Route::post('/bookings', [FrontendUserController::class, 'storeBooking'])->name('bookings.store');
    Route::get('/bookings/{id}/edit', [FrontendUserController::class, 'editBooking'])->name('bookings.edit');
    Route::put('/bookings/{id}', [FrontendUserController::class, 'updateBooking'])->name('bookings.update');
    Route::delete('/bookings/{id}', [FrontendUserController::class, 'cancelBooking'])->name('bookings.cancel');
});
Route::get('/admin/bookings/busy-times', [
    App\Http\Controllers\Admin\BookingController::class,
    'busyTimes'
])->name('admin.bookings.busy-times');
