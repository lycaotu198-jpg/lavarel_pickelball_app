@extends('Admin.layout.app')

@section('title', 'Quản lý đặt sân')

@section('content')
<div class="container-fluid py-4">
    <!-- Header & Breadcrumbs -->
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h4 class="fw-bold text-dark mb-1">📅 Quản lý đặt sân</h4>
            <nav aria-label="breadcrumb">
                <ol class="breadcrumb mb-0">
                    <li class="breadcrumb-item"><a href="#" class="text-decoration-none">Admin</a></li>
                    <li class="breadcrumb-item active">Đặt sân</li>
                </ol>
            </nav>
        </div>
        <div class="d-flex gap-2">
            <a href="{{ route('admin.bookings.create') }}" class="btn btn-primary shadow-sm px-3 rounded-3">
                <i class="fas fa-plus-circle me-1"></i> Đặt sân lẻ
            </a>
            <a href="{{ route('admin.monthly-rentals.index') }}" class="btn btn-success shadow-sm px-3 rounded-3">
                <i class="fas fa-calendar-alt me-1"></i> Thuê theo tháng
            </a>
        </div>
    </div>

    <!-- Quick Stats -->
    <div class="row mb-4">
        <div class="col-md-3">
            <div class="stat-card p-3 shadow-sm border-0 card h-100">
                <div class="d-flex align-items-center">
                    <div class="icon-shape bg-soft-primary text-primary me-3">
                        <i class="fas fa-calendar-check"></i>
                    </div>
                    <div>
                        <p class="text-muted mb-0 small fw-bold">Tổng đơn hôm nay</p>
                        <h5 class="mb-0 fw-bold">{{ $bookings->where('booking_date', date('Y-m-d'))->count() }}</h5>
                    </div>
                </div>
            </div>
        </div>
        <!-- Thêm các thẻ stats khác nếu cần -->
    </div>

    <!-- Main Table Card -->
    <div class="card border-0 shadow-sm rounded-4 overflow-hidden">
        <div class="card-header bg-white py-3 border-bottom">
            <div class="row align-items-center">
                <div class="col">
                    <h6 class="mb-0 fw-bold text-dark">Danh sách đặt sân gần đây</h6>
                </div>
                <div class="col text-end">
                    <button class="btn btn-light btn-sm rounded-pill px-3">
                        <i class="fas fa-filter me-1"></i> Bộ lọc
                    </button>
                </div>
            </div>
        </div>
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0">
                    <thead class="bg-light">
                        <tr>
                            <th class="ps-4 py-3 border-0 text-secondary small text-uppercase">ID</th>
                            <th class="py-3 border-0 text-secondary small text-uppercase">Khách & Sân</th>
                            <th class="py-3 border-0 text-secondary small text-uppercase text-center">Thời gian</th>
                            <th class="py-3 border-0 text-secondary small text-uppercase text-center">Loại</th>
                            <th class="py-3 border-0 text-secondary small text-uppercase">Thành tiền</th>
                            <th class="py-3 border-0 text-secondary small text-uppercase text-center">Trạng thái</th>
                            <th class="py-3 border-0 text-secondary small text-uppercase text-end pe-4">Hành động</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($bookings as $booking)
                        <tr>
                            <td class="ps-4">
                                <span class="text-muted fw-bold">#{{ $booking->id }}</span>
                            </td>
                            <td>
                                <div class="d-flex align-items-center">
                                    <div class="avatar-sm bg-soft-info text-info me-3 rounded-circle d-flex align-items-center justify-content-center fw-bold">
                                        {{ substr($booking->user->name, 0, 1) }}
                                    </div>
                                    <div>
                                        <div class="fw-bold text-dark">{{ $booking->user->name }}</div>
                                        <div class="small text-muted"><i class="fas fa-volleyball-ball me-1"></i> {{ $booking->court->name }}</div>
                                    </div>
                                </div>
                            </td>
                            <td class="text-center">
                                <div class="fw-bold text-dark">{{ \Carbon\Carbon::parse($booking->booking_date)->format('d/m/Y') }}</div>
                                <div class="small text-primary fw-medium">{{ $booking->start_time }} - {{ $booking->end_time }}</div>
                            </td>
                            <td class="text-center">
                                @if($booking->monthly_rental_id)
                                    <span class="badge badge-soft-success rounded-pill">Thuê tháng</span>
                                @else
                                    <span class="badge badge-soft-primary rounded-pill">Theo giờ</span>
                                @endif
                            </td>
                            <td>
                                <div class="fw-bold text-dark">{{ number_format($booking->total_price) }}đ</div>
                            </td>
                            <td class="text-center">
                                @php
                                    $statusClass = match($booking->status) {
                                        'pending' => 'badge-soft-warning',
                                        'confirmed' => 'badge-soft-success',
                                        'cancelled' => 'badge-soft-danger',
                                        default => 'badge-soft-secondary'
                                    };
                                @endphp
                                <span class="badge {{ $statusClass }} rounded-pill text-capitalize">
                                    {{ $booking->status }}
                                </span>
                            </td>
                            <td class="text-end pe-4">
                                <div class="d-flex justify-content-end gap-2">
                                    @if(!$booking->monthly_rental_id && (!$booking->payment || $booking->payment->status !== 'paid'))
                                        <a href="{{ route('admin.bookings.edit', $booking->id) }}"

                                           class="btn btn-soft-warning btn-icon" title="Chỉnh sửa">
                                            <i class="fas fa-edit"></i>
                                        </a>
                                    @endif
                                    <a href="{{ route('admin.bookings.delete', $booking->id) }}"
                                       class="btn btn-soft-danger btn-icon"
                                       onclick="return confirm('Bạn có chắc chắn muốn xoá?')" title="Xóa">
                                        <i class="fas fa-trash"></i>
                                    </a>
                                </div>
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="7" class="text-center py-5">
                                <img src="https://cdn-icons-png.flaticon.com/512/7486/7486744.png" width="80" class="mb-3 opacity-50">
                                <h6 class="text-muted">Chưa có lịch đặt sân nào được ghi nhận</h6>
                            </td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<style>
    :root {
        --primary-color: #4f46e5;
        --soft-primary: #eef2ff;
        --soft-success: #ecfdf5;
        --soft-warning: #fffbeb;
        --soft-danger: #fef2f2;
        --soft-info: #e0f2fe;
    }

    /* Card & Stats */
    .stat-card { border-radius: 16px; transition: transform 0.2s; }
    .stat-card:hover { transform: translateY(-3px); }
    .icon-shape {
        width: 45px; height: 45px; border-radius: 12px;
        display: flex; align-items: center; justify-content: center; font-size: 1.2rem;
    }

    /* Table Styles */
    .table thead th { font-weight: 700; font-size: 0.75rem; letter-spacing: 0.05em; }
    .avatar-sm { width: 35px; height: 35px; font-size: 0.8rem; }

    /* Soft Badges */
    .badge-soft-primary { background: var(--soft-primary); color: #4f46e5; }
    .badge-soft-success { background: var(--soft-success); color: #10b981; }
    .badge-soft-warning { background: var(--soft-warning); color: #f59e0b; }
    .badge-soft-danger  { background: var(--soft-danger); color: #ef4444; }
    .badge-soft-secondary { background: #f3f4f6; color: #6b7280; }
    .badge-soft-info { background: var(--soft-info); color: #0ea5e9; }

    /* Action Buttons */
    .btn-icon {
        width: 32px; height: 32px; padding: 0;
        display: inline-flex; align-items: center; justify-content: center;
        border-radius: 8px; border: none; transition: 0.2s;
    }
    .btn-soft-warning { background: var(--soft-warning); color: #d97706; }
    .btn-soft-warning:hover { background: #f59e0b; color: white; }
    .btn-soft-danger { background: var(--soft-danger); color: #dc2626; }
    .btn-soft-danger:hover { background: #dc2626; color: white; }

    /* Animation */
    tbody tr { transition: background-color 0.2s; }
    .bg-soft-info { background: var(--soft-info); }
    .bg-soft-primary { background: var(--soft-primary); }
</style>
@endsection
