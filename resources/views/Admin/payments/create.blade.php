@extends('Admin.layout.app')

@section('title', 'Tạo hóa đơn thủ công')

@section('content')

<h3 class="mb-4">🧾 Tạo hóa đơn thanh toán thủ công</h3>

@if ($errors->any())
    <div class="alert alert-danger">
        {{ $errors->first() }}
    </div>
@endif

<div class="card shadow-sm">
    <div class="card-body">

        <form action="{{ route('admin.payments.storeManual') }}" method="POST">
            @csrf

            {{-- CHỌN BOOKING --}}
            <div class="mb-3">
                <label class="form-label fw-bold">Booking</label>
                <select name="booking_id" class="form-select" required>
                    <option value="">-- Chọn booking --</option>

                    @foreach($bookings as $booking)
                        <option value="{{ $booking->id }}">
                            #{{ $booking->id }} |
                            {{ $booking->user->name }} |
                            {{ $booking->court->name }} |
                            {{ $booking->booking_date }}
                            ({{ $booking->start_time }} - {{ $booking->end_time }}) |
                            {{ number_format($booking->total_price) }} đ
                        </option>
                    @endforeach
                </select>
            </div>

            {{-- PHƯƠNG THỨC THANH TOÁN --}}
            <div class="mb-3">
                <label class="form-label fw-bold">Phương thức thanh toán</label>
                <select name="method" class="form-select" required>
                    <option value="cash">💵 Tiền mặt</option>
                    <option value="bank_transfer">🏦 Chuyển khoản</option>
                    <option value="momo">📱 MoMo</option>
                    <option value="vnpay">💳 VNPay</option>
                </select>
            </div>

            {{-- BUTTON --}}
            <div class="text-end">
                <a href="{{ route('admin.payments.index') }}" class="btn btn-secondary">
                    ⬅ Quay lại
                </a>

                <button type="submit" class="btn btn-success">
                    ✅ Tạo hóa đơn & xác nhận thanh toán
                </button>
            </div>

        </form>

    </div>
</div>

@endsection
