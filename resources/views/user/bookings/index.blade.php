@extends('user.layout.app')

@section('title', 'Lịch sử đặt sân')

@section('content')
<div class="container-fluid py-4">
    <!-- Header Section -->
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h4 class="fw-bold text-dark mb-1">Lịch sử đặt sân</h4>
            <p class="text-muted mb-0">Quản lý và theo dõi các trận đấu của bạn</p>
        </div>
        <a href="{{ route('user.courts') }}" class="btn btn-primary shadow-sm px-4 rounded-pill">
            <i class="fas fa-plus me-2"></i>Đặt sân mới
        </a>
    </div>

    <!-- Main Card -->
    <div class="card border-0 shadow-sm rounded-4 overflow-hidden">
        <div class="card-body p-0">
            @if($bookings->count() > 0)
                <div class="table-responsive">
                    <table class="table table-hover align-middle mb-0">
                        <thead class="bg-light">
                            <tr>
                                <th class="ps-4 py-3 border-0 text-secondary small text-uppercase">Thông tin sân</th>
                                <th class="py-3 border-0 text-secondary small text-uppercase">Thời gian</th>
                                <th class="py-3 border-0 text-secondary small text-uppercase">Thanh toán</th>
                                <th class="py-3 border-0 text-secondary small text-uppercase">Trạng thái</th>
                                <th class="py-3 border-0 text-secondary small text-uppercase text-end pe-4">Thao tác</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($bookings as $booking)
                                @php
                                    $startDT = \Carbon\Carbon::parse($booking->booking_date . ' ' . $booking->start_time);
                                    $endDT = \Carbon\Carbon::parse($booking->booking_date . ' ' . $booking->end_time);
                                    $now = now();

                                    $isPaid = $booking->payment && $booking->payment->status === 'paid';
                                    $isCancelled = $booking->status === 'cancelled';
                                @endphp
                                <tr>
                                    <td class="ps-4 py-3">
                                        <div class="d-flex align-items-center">
                                            <div class="icon-box bg-soft-primary text-primary me-3">
                                                <i class="fas fa-skating"></i>
                                            </div>
                                            <div>
                                                <div class="fw-bold text-dark">{{ $booking->court->name }}</div>
                                                <div class="small text-muted"><i class="fas fa-map-marker-alt me-1"></i>{{ $booking->court->location }}</div>
                                            </div>
                                        </div>
                                    </td>
                                    <td class="py-3">
                                        <div class="fw-bold text-dark">{{ $startDT->format('d/m/Y') }}</div>
                                        <div class="small text-muted">
                                            <i class="far fa-clock me-1"></i>{{ $startDT->format('H:i') }} - {{ $endDT->format('H:i') }}
                                        </div>
                                    </td>
                                    <td class="py-3">
                                        <div class="fw-bold text-primary">{{ number_format($booking->total_price) }}đ</div>
                                        @if($booking->payment)
                                            <span class="status-dot {{ $booking->payment->status === 'paid' ? 'bg-success' : 'bg-warning' }}"></span>
                                            <small class="text-capitalize">{{ $booking->payment->status === 'paid' ? 'Đã trả' : 'Chưa trả' }}</small>
                                        @endif
                                    </td>
                                    <td class="py-3">
                                        @if($isCancelled)
                                            <span class="badge badge-soft-danger">Đã hủy</span>
                                        @elseif($booking->status === 'pending')
                                            <span class="badge badge-soft-warning">Chờ xác nhận</span>
                                        @elseif($endDT->isPast())
                                            <span class="badge badge-soft-secondary">Đã kết thúc</span>
                                        @elseif($now->between($startDT, $endDT))
                                            <span class="badge badge-soft-primary animate-pulse">Đang diễn ra</span>
                                        @else
                                            <span class="badge badge-soft-success">Đã xác nhận</span>
                                        @endif
                                    </td>
                                    <td class="py-3 text-end pe-4">
                                        <div class="d-flex justify-content-end gap-2">
                                            @if(!$isCancelled && !$startDT->isPast() && !$isPaid)
                                                <a href="{{ route('user.bookings.edit', $booking->id) }}" class="btn btn-sm btn-light-primary btn-icon" title="Sửa">
                                                    <i class="fas fa-edit"></i>
                                                </a>
                                                <button type="button" class="btn btn-sm btn-light-danger btn-icon" title="Hủy"
                                                        onclick="confirmCancel({{ $booking->id }}, '{{ $booking->court->name }}')">
                                                    <i class="fas fa-times"></i>
                                                </button>
                                            @else
                                                <span class="text-muted small">N/A</span>
                                            @endif
                                        </div>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
                <!-- Pagination -->
                <div class="p-4 border-top">
                    {{ $bookings->links('pagination::bootstrap-5') }}
                </div>
            @else
                <div class="text-center py-5">
                    <div class="empty-state-icon mb-3">
                        <i class="fas fa-calendar-times text-muted opacity-25 fa-4x"></i>
                    </div>
                    <h5 class="text-dark fw-bold">Chưa có lịch đặt sân</h5>
                    <p class="text-muted">Lịch sử đặt sân của bạn sẽ xuất hiện tại đây.</p>
                    <a href="{{ route('user.courts') }}" class="btn btn-primary px-4 rounded-pill">Khám phá sân ngay</a>
                </div>
            @endif
        </div>
    </div>
</div>

<!-- Modal Hủy (Giữ nguyên logic của bạn nhưng làm đẹp UI) -->
<div class="modal fade" id="cancelModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content border-0 shadow-lg rounded-4">
            <div class="modal-body p-4 text-center">
                <div class="icon-box bg-soft-danger text-danger mx-auto mb-3" style="width: 70px; height: 70px; font-size: 2rem;">
                    <i class="fas fa-exclamation-triangle"></i>
                </div>
                <h5 class="fw-bold text-dark">Xác nhận hủy đặt sân?</h5>
                <p class="text-muted">Bạn có chắc chắn muốn hủy đặt sân tại <strong id="cancelCourtName"></strong>? Hành động này không thể hoàn tác.</p>

                <div class="d-flex gap-2 justify-content-center mt-4">
                    <button type="button" class="btn btn-light px-4 rounded-pill" data-bs-dismiss="modal">Quay lại</button>
                    <form id="cancelForm" method="POST">
                        @csrf
                        @method('DELETE')
                        <button type="submit" class="btn btn-danger px-4 rounded-pill">Xác nhận hủy</button>
                    </form>
                </div>
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
        --soft-secondary: #f3f4f6;
    }

    /* Table Styling */
    .table thead th { letter-spacing: 0.05em; font-size: 0.75rem; }
    .table tbody tr { transition: all 0.2s; }
    .table tbody tr:hover { background-color: #f8fafc; }

    /* Icon Box */
    .icon-box {
        width: 42px;
        height: 42px;
        border-radius: 12px;
        display: flex;
        align-items: center;
        justify-content: center;
        flex-shrink: 0;
    }
    .bg-soft-primary { background: var(--soft-primary); }
    .bg-soft-danger { background: var(--soft-danger); }

    /* Badges */
    .badge { padding: 0.6em 1em; border-radius: 8px; font-weight: 600; }
    .badge-soft-success { background: var(--soft-success); color: #059669; }
    .badge-soft-warning { background: var(--soft-warning); color: #d97706; }
    .badge-soft-danger { background: var(--soft-danger); color: #dc2626; }
    .badge-soft-primary { background: var(--soft-primary); color: var(--primary-color); }
    .badge-soft-secondary { background: var(--soft-secondary); color: #6b7280; }

    /* Button Icons */
    .btn-icon { width: 32px; height: 32px; display: flex; align-items: center; justify-content: center; padding: 0; border-radius: 8px; border: none; }
    .btn-light-primary { background: var(--soft-primary); color: var(--primary-color); }
    .btn-light-primary:hover { background: var(--primary-color); color: white; }
    .btn-light-danger { background: var(--soft-danger); color: #dc2626; }
    .btn-light-danger:hover { background: #dc2626; color: white; }

    /* Status Dot */
    .status-dot { width: 8px; height: 8px; border-radius: 50%; display: inline-block; margin-right: 4px; }

    /* Animation for Live Status */
    .animate-pulse { animation: pulse 2s cubic-bezier(0.4, 0, 0.6, 1) infinite; }
    @keyframes pulse { 0%, 100% { opacity: 1; } 50% { opacity: .5; } }

    /* Pagination Customization */
    .pagination { margin-bottom: 0; gap: 5px; }
    .page-link { border: none; border-radius: 8px; color: var(--primary-color); padding: 8px 16px; }
    .page-item.active .page-link { background-color: var(--primary-color); }
</style>

<script>
function confirmCancel(bookingId, courtName) {
    document.getElementById('cancelCourtName').textContent = courtName;
    document.getElementById('cancelForm').action = `/user/bookings/${bookingId}`;
    const modal = new bootstrap.Modal(document.getElementById('cancelModal'));
    modal.show();
}
</script>
@endsection
