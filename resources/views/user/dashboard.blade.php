@extends('user.layout.app')

@section('title', 'Bảng điều khiển')

@section('content')
<div class="container-fluid py-4">
    <!-- Welcome Section -->
    <div class="row mb-4">
        <div class="col-12">
            <div class="welcome-banner p-4 p-md-5 text-white shadow-sm position-relative overflow-hidden">
                <div class="row align-items-center position-relative" style="z-index: 2;">
                    <div class="col-md-8">
                        <span class="badge bg-white-50 text-white mb-2 px-3 py-2 rounded-pill">Hệ thống đặt sân chuyên nghiệp</span>
                        <h2 class="display-6 fw-bold mb-1">Chào mừng trở lại, {{ Auth::user()->name }}!</h2>
                        <p class="lead opacity-75 mb-0">Hôm nay bạn muốn chơi ở sân nào? Kiểm tra lịch và đặt ngay nhé.</p>
                    </div>
                    <div class="col-md-4 text-end d-none d-md-block">
                        <img src="https://cdn-icons-png.flaticon.com/512/3064/3064211.png" alt="Sport Icon" class="img-fluid banner-img">
                    </div>
                </div>
                <!-- Trang trí nền -->
                <div class="circle-decoration decoration-1"></div>
                <div class="circle-decoration decoration-2"></div>
            </div>
        </div>
    </div>

    <!-- Statistics Cards -->
    <div class="row mb-4">
        <div class="col-lg-4 col-md-6 mb-3">
            <div class="stat-card p-4 h-100 shadow-sm border-0 card">
                <div class="d-flex justify-content-between align-items-start">
                    <div>
                        <p class="text-muted fw-medium mb-1">Lịch sử đặt sân</p>
                        <h3 class="fw-bold mb-0">{{ $totalBookings }} <small class="fs-6 fw-normal text-muted">lượt</small></h3>
                    </div>
                    <div class="stat-icon bg-soft-primary">
                        <i class="fas fa-history text-primary"></i>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-lg-4 col-md-6 mb-3">
            <div class="stat-card p-4 h-100 shadow-sm border-0 card">
                <div class="d-flex justify-content-between align-items-start">
                    <div>
                        <p class="text-muted fw-medium mb-1">Đặt sân sắp tới</p>
                        <h3 class="fw-bold mb-0 text-warning">{{ $upcomingBookings }} <small class="fs-6 fw-normal text-muted">trận</small></h3>
                    </div>
                    <div class="stat-icon bg-soft-warning">
                        <i class="fas fa-clock text-warning"></i>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-lg-4 col-md-12 mb-3">
            <div class="stat-card p-4 h-100 shadow-sm border-0 card">
                <div class="d-flex justify-content-between align-items-start">
                    <div>
                        <p class="text-muted fw-medium mb-1">Tổng chi tiêu</p>
                        <h3 class="fw-bold mb-0 text-success">{{ number_format($totalSpent) }} <small class="fs-6 fw-normal text-muted">VND</small></h3>
                    </div>
                    <div class="stat-icon bg-soft-success">
                        <i class="fas fa-wallet text-success"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Quick Actions -->
    <div class="row mb-4">
        <div class="col-12">
            <h5 class="fw-bold mb-3 text-dark">Thao tác nhanh</h5>
            <div class="row g-3">
                <div class="col-6 col-md-3">
                    <a href="{{ route('user.courts') }}" class="action-card card border-0 shadow-sm p-3 text-center text-decoration-none">
                        <div class="action-icon bg-primary text-white mx-auto mb-2">
                            <i class="fas fa-plus"></i>
                        </div>
                        <span class="fw-bold text-dark">Đặt sân mới</span>
                    </a>
                </div>
                <div class="col-6 col-md-3">
                    <a href="{{ route('user.bookings') }}" class="action-card card border-0 shadow-sm p-3 text-center text-decoration-none">
                        <div class="action-icon bg-info text-white mx-auto mb-2">
                            <i class="fas fa-tasks"></i>
                        </div>
                        <span class="fw-bold text-dark">Quản lý đặt sân</span>
                    </a>
                </div>
                <div class="col-6 col-md-3">
                    <a href="{{ route('user.map') }}" class="action-card card border-0 shadow-sm p-3 text-center text-decoration-none">
                        <div class="action-icon bg-success text-white mx-auto mb-2">
                            <i class="fas fa-map-marker-alt"></i>
                        </div>
                        <span class="fw-bold text-dark">Xem bản đồ</span>
                    </a>
                </div>
                <div class="col-6 col-md-3">
                    <a href="{{ route('user.profile') }}" class="action-card card border-0 shadow-sm p-3 text-center text-decoration-none">
                        <div class="action-icon bg-secondary text-white mx-auto mb-2">
                            <i class="fas fa-user-gear"></i>
                        </div>
                        <span class="fw-bold text-dark">Cài đặt tài khoản</span>
                    </a>
                </div>
            </div>
        </div>
    </div>

    <!-- Recent Bookings Table -->
    <div class="row">
        <div class="col-12">
            <div class="card border-0 shadow-sm">
                <div class="card-header bg-white py-3 d-flex justify-content-between align-items-center">
                    <h5 class="fw-bold mb-0 text-dark">
                        <i class="fas fa-calendar-check me-2 text-primary"></i>Các trận đấu sắp tới
                    </h5>
                    <a href="{{ route('user.bookings') }}" class="btn btn-sm btn-light text-primary fw-bold">Xem tất cả</a>
                </div>
                <div class="card-body p-0">
                    @if($recentBookings->count() > 0)
                        <div class="table-responsive">
                            <table class="table table-hover align-middle mb-0">
                                <thead class="bg-light">
                                    <tr>
                                        <th class="ps-4 border-0">Thông tin sân</th>
                                        <th class="border-0">Thời gian</th>
                                        <th class="border-0">Trạng thái</th>
                                        <th class="border-0 text-end pe-4">Hành động</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($recentBookings as $booking)
                                        <tr>
                                            <td class="ps-4">
                                                <div class="d-flex align-items-center">
                                                    <div class="court-img-mini me-3 rounded bg-soft-primary d-flex align-items-center justify-content-center">
                                                        <i class="fas fa-volleyball-ball text-primary"></i>
                                                    </div>
                                                    <div>
                                                        <div class="fw-bold text-dark">{{ $booking->court->name }}</div>
                                                        <small class="text-muted"><i class="fas fa-map-pin me-1"></i>{{ $booking->court->location }}</small>
                                                    </div>
                                                </div>
                                            </td>
                                            <td>
                                                <div class="fw-bold text-dark">{{ \Carbon\Carbon::parse($booking->booking_date)->format('d/m/Y') }}</div>
                                                <div class="small text-muted">{{ \Carbon\Carbon::parse($booking->start_time)->format('H:i') }} - {{ \Carbon\Carbon::parse($booking->end_time)->format('H:i') }}</div>
                                            </td>
                                            <td>
                                                @switch($booking->status)
                                                    @case('pending')
                                                        <span class="badge badge-soft-warning">Chờ thanh toán</span>
                                                        @break
                                                    @case('confirmed')
                                                        <span class="badge badge-soft-success">Đã xác nhận</span>
                                                        @break
                                                    @case('cancelled')
                                                        <span class="badge badge-soft-danger">Đã hủy</span>
                                                        @break
                                                @endswitch
                                            </td>
                                            <td class="text-end pe-4">
                                                @if($booking->status !== 'cancelled' && (!$booking->payment || $booking->payment->status !== 'paid'))
                                                    <a href="{{ route('user.bookings.edit', $booking->id) }}" class="btn btn-icon-only btn-soft-primary">
                                                        <i class="fas fa-edit"></i>
                                                    </a>
                                                @else
                                                    <button disabled class="btn btn-icon-only btn-light"><i class="fas fa-check"></i></button>
                                                @endif
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    @else
                        <div class="text-center py-5">
                            <img src="https://cdn-icons-png.flaticon.com/512/7486/7486744.png" width="80" class="mb-3 opacity-50">
                            <h6 class="text-muted">Bạn chưa có lịch đặt sân nào sắp tới</h6>
                            <a href="{{ route('user.courts') }}" class="btn btn-primary mt-2">Đặt sân ngay thôi!</a>
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
</div>

<style>
    :root {
        --primary-gradient: linear-gradient(135deg, #3cce5b 0%, #7c3aed 100%);
        --soft-primary: #eef2ff;
        --soft-success: #ecfdf5;
        --soft-warning: #fffbeb;
        --soft-danger: #fef2f2;
    }

    /* Welcome Banner */
    .welcome-banner {
        background: var(--primary-gradient);
        border-radius: 20px;
        border: none;
    }

    .banner-img {
        max-height: 120px;
        filter: drop-shadow(0 10px 15px rgba(0,0,0,0.2));
    }

    .circle-decoration {
        position: absolute;
        border-radius: 50%;
        background: rgba(255,255,255,0.1);
    }
    .decoration-1 { width: 200px; height: 200px; top: -50px; right: -50px; }
    .decoration-2 { width: 100px; height: 100px; bottom: -20px; left: 10%; }

    /* Stat Cards */
    .stat-card {
        border-radius: 16px;
        transition: transform 0.3s ease;
    }
    .stat-card:hover { transform: translateY(-5px); }

    .stat-icon {
        width: 48px;
        height: 48px;
        border-radius: 12px;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 1.25rem;
    }

    /* Action Cards */
    .action-card {
        border-radius: 16px;
        transition: all 0.3s ease;
    }
    .action-card:hover {
        background: var(--soft-primary);
        transform: translateY(-5px);
    }
    .action-icon {
        width: 50px;
        height: 50px;
        border-radius: 50%;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 1.2rem;
        box-shadow: 0 4px 10px rgba(0,0,0,0.1);
    }

    /* Table Styling */
    .table thead th {
        font-size: 0.75rem;
        text-transform: uppercase;
        letter-spacing: 0.05em;
        padding: 1rem;
    }
    .court-img-mini {
        width: 40px;
        height: 40px;
    }

    /* Soft Badges */
    .badge-soft-success { background: var(--soft-success); color: #059669; }
    .badge-soft-warning { background: var(--soft-warning); color: #d97706; }
    .badge-soft-danger  { background: var(--soft-danger); color: #dc2626; }
    .badge-soft-primary { background: var(--soft-primary); color: #3add19; }

    /* Utility */
    .bg-soft-primary { background: var(--soft-primary); }
    .bg-soft-success { background: var(--soft-success); }
    .bg-soft-warning { background: var(--soft-warning); }

    .btn-icon-only {
        width: 38px;
        height: 38px;
        padding: 0;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        border-radius: 10px;
    }
    .btn-soft-primary {
        background: var(--soft-primary);
        color: #4f46e5;
        border: none;
    }
    .btn-soft-primary:hover {
        background: #4f46e5;
        color: white;
    }
</style>
@endsection
