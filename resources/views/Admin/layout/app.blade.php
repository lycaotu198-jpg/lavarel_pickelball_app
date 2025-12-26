<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <title>@yield('title', 'Admin Dashboard')</title>

    <!-- Bootstrap -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
     <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>@yield('title')</title>

    <!-- 1. Bootstrap 5 CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">

    <!-- 2. Font Awesome (Quan trọng để hiện icon fas fa-...) -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.2/css/all.min.css">

    <!-- 3. Google Fonts (Để giao diện hiện đại hơn) -->
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
    @stack('styles')

    <style>
        body { background-color: #f4f6f9; }
        .sidebar {
            width: 230px;
            min-height: 100vh;
            background: #1f2937;
        }
        .sidebar a {
            color: #cbd5e1;
            text-decoration: none;
            display: block;
            padding: 12px 16px;
        }
        .sidebar a:hover {
            background: #374151;
            color: #fff;
        }

         body { background-color: #f4f6f9; }

        .sidebar {
            width: 230px;
            min-height: 100vh;
            background: #1f2937;
        }

        .sidebar a,
        .sidebar button {
            color: #cbd5e1;
            text-decoration: none;
            display: block;
            padding: 12px 16px;
            width: 100%;
            background: none;
            border: none;
            text-align: left;
        }

        .sidebar a:hover,
        .sidebar button:hover {
            background: #374151;
            color: #fff;
        }

        .logout-btn {
            cursor: pointer;
        }

        .time-slot {
            padding: 8px 12px;
            margin: 2px;
            border-radius: 5px;
            display: inline-block;
            cursor: pointer;
            transition: all 0.3s ease;
            user-select: none;
            border: 2px solid #dee2e6;
        }

        .time-slot.available {
            background-color: #d4edda;
            color: #155724;
            border-color: #c3e6cb;
        }

        .time-slot.booked {
            background-color: #f8d7da;
            color: #721c24;
            border-color: #f5c6cb;
            cursor: not-allowed;
        }

        .time-slot.selected {
            background-color: #0d6efd;
            color: #fff;
            border-color: #0d6efd;
        }

        .time-slot.selection-start {
            background-color: #fff3cd;
            color: #856404;
            border-color: #ffeaa7;
        }

        .btn-warning {
            background-color: #f39c12;
            border-color: #f39c12;
        }

        .btn-warning:hover {
            background-color: #e67e22;
            border-color: #e67e22;
        }

    </style>
</head>
@stack('scripts')

<body>

<div class="d-flex">
    <!-- Sidebar -->
    <div class="sidebar">
        <a href="{{ route('admin.dashboard.courts') }}"><h5 class="text-white text-center py-3 border-bottom">ADMIN</h5></a>

        <a href="{{ route('admin.courts.index') }}">🏓 Quản lý sân</a>
        <a href="{{ route('admin.bookings.index') }}">📅 Quản lý đặt sân</a>
        <a href="{{ route('admin.payments.index') }}">💳 Thanh toán</a>
        <a href="{{ route('admin.users.index') }}">👤 Người dùng</a>
       <form id="logout-form" action="{{ route('admin.logout') }}" method="POST">
            @csrf
            <button type="submit" class="btn btn-danger"
                    onclick="return confirm('Bạn có chắc chắn muốn đăng xuất không?')">
                🚪 Đăng xuất
            </button>
        </form>

    </div>


    <!-- Main content -->
    <div class="flex-grow-1 p-4">
        @yield('content')
    </div>
</div>
  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>


</body>
</html>
