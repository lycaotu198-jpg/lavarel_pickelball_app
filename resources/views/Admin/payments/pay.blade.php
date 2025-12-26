@extends('Admin.layout.app')

@section('title', 'Thanh toán QR')

@section('content')

<h3 class="mb-4">💳 Thanh toán chuyển khoản BIDV</h3>

<div class="row">
    <div class="col-md-5">
        <div class="card shadow-sm">
            <div class="card-body text-center">

                <p><strong>Số tiền:</strong></p>
                <h4 class="text-danger">
                    {{ number_format($booking->total_price) }} đ
                </h4>

                <p class="mt-3">📱 Quét mã QR để thanh toán</p>

                <img
                    src="https://img.vietqr.io/image/BIDV-123456789-print.png
                    ?amount={{ $booking->total_price }}
                    &addInfo=BOOKING_{{ $booking->id }}
                    &accountName=NGUYEN%20VAN%20A"
                    class="img-fluid border rounded"
                >

                <p class="mt-3 text-muted">
                    Nội dung chuyển khoản:<br>
                    <strong>BOOKING_{{ $booking->id }}</strong>
                </p>

            </div>
        </div>
    </div>

    <div class="col-md-7">
        <div class="alert alert-warning">
            ⚠️ Sau khi chuyển khoản, vui lòng chờ admin xác nhận thanh toán
        </div>

        <form action="{{ route('admin.payments.confirm', $booking->id) }}" method="POST">
            @csrf
            <button class="btn btn-success">
                ✅ Khách đã chuyển khoản
            </button>
        </form>
    </div>
</div>

@endsection
