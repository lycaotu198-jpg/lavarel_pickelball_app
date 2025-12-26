@extends('Admin.layout.app')

@section('title', 'Quản lý thanh toán')

@section('content')

<div class="d-flex justify-content-between align-items-center mb-3">
    <h3>💳 Quản lý thanh toán Booking</h3>


</div>

{{-- Thông báo thành công --}}
@if(session('success'))
    <div class="alert alert-success">
        {{ session('success') }}
    </div>
@endif

<div class="card shadow-sm">
    <div class="card-body">
        <table class="table table-bordered table-hover align-middle">
            <thead class="table-dark">
                <tr>
                    <th>#</th>
                    <th>Khách hàng</th>
                    <th>Sân</th>
                    <th>Số tiền</th>
                    <th>Phương thức</th>
                    <th>Trạng thái</th>
                    <th>Ngày thanh toán</th>
                    <th width="160">Thao tác</th>
                </tr>
            </thead>
            <tbody>
                @forelse($payments as $payment)
                    <tr>
                        <td>{{ $payment->id }}</td>

                        <td>
                            {{ $payment->booking->user->name ?? 'N/A' }}
                        </td>

                        <td>
                            {{ $payment->booking->court->name ?? 'N/A' }}
                        </td>

                        <td class="text-end">
                            <strong>{{ number_format($payment->amount) }} đ</strong>
                        </td>

                        <td>
                            @switch($payment->method)
                                @case('cash') Tiền mặt @break
                                @case('bank_transfer') Chuyển khoản @break
                                @case('momo') MoMo @break
                                @case('vnpay') VNPay @break
                                @default ---
                            @endswitch
                        </td>

                        <td>
                            @if($payment->status === 'paid')
                                <span class="badge bg-success">Đã thanh toán</span>
                            @elseif($payment->status === 'unpaid')
                                <span class="badge bg-warning text-dark">Chưa thanh toán</span>
                            @else
                                <span class="badge bg-danger">Thất bại</span>
                            @endif
                        </td>

                        <td>
                            {{ $payment->paid_at ? $payment->paid_at->format('d/m/Y H:i') : '-' }}
                        </td>

                        <td class="text-center">
                            @if($payment->status !== 'paid')
                                <a href="{{ route('admin.payments.pay', $payment->booking_id) }}"
                                   class="btn btn-sm btn-success">
                                    💰 Thanh toán
                                </a>
                            @else
                                <span class="text-muted">—</span>
                            @endif
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="8" class="text-center text-muted">
                            Chưa có dữ liệu thanh toán
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>

@endsection
