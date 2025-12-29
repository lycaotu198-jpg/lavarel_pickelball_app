@extends('user.layout.app')

@section('title', 'Đặt sân - ' . $court->name)

@section('content')
<div class="container-fluid">
    <div class="row justify-content-center">
        <div class="col-lg-8">
            <div class="card">
                <div class="card-header">
                    <h4 class="card-title mb-0">
                        <i class="fas fa-calendar-plus me-2"></i>Đặt sân: {{ $court->name }}
                    </h4>
                </div>
                <div class="card-body">
                    <!-- Important Notice -->
                    <div class="alert alert-info">
                        <h6 class="alert-heading">
                            <i class="fas fa-info-circle me-2"></i>Lưu ý quan trọng
                        </h6>
                        <ul class="mb-0">
                            <li>Đặt sân trước ít nhất 1 giờ</li>
                            <li>Sau khi đặt sân, bạn cần thanh toán để xác nhận</li>
                            <li>Chỉ có thể hủy hoặc chỉnh sửa đặt sân khi chưa thanh toán</li>
                        </ul>
                    </div>

                    <!-- Court Info -->
                    <div class="row mb-4">
                        <div class="col-md-6">
                            <div class="card bg-light">
                                <div class="card-body">
                                    <h6 class="card-title">
                                        <i class="fas fa-map-marker-alt me-1"></i>Thông tin sân
                                    </h6>
                                    <p class="mb-1"><strong>Tên sân:</strong> {{ $court->name }}</p>
                                    <p class="mb-1"><strong>Vị trí:</strong> {{ $court->location }}</p>
                                    <p class="mb-1"><strong>Giá:</strong> {{ number_format($court->price_per_hour) }} VND/giờ</p>
                                    <p class="mb-0"><strong>Trạng thái:</strong>
                                        <span class="badge bg-success">Hoạt động</span>
                                    </p>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="card bg-info text-white">
                                <div class="card-body">
                                    <h6 class="card-title">
                                        <i class="fas fa-clock me-1"></i>Giờ hoạt động
                                    </h6>
                                    <p class="mb-0">5:00 AM - 10:00 PM</p>
                                    <small>Đặt sân trước ít nhất 1 giờ</small>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Booking Form -->
                    <form method="POST" action="{{ route('user.bookings.store') }}" id="bookingForm">
                        @csrf

                        <input type="hidden" name="court_id" value="{{ $court->id }}">

                     <div class="row">
                        <!-- Date Selection -->
                        <div class="col-md-6 mb-3">
                            <label for="booking_date" class="form-label">
                                <i class="fas fa-calendar me-1"></i>Ngày đặt sân <span class="text-danger">*</span>
                            </label>
                            <input type="date"
                                class="form-control @error('booking_date') is-invalid @enderror"
                                id="booking_date"
                                name="booking_date"
                                value="{{ old('booking_date', $preFill['date']) }}"
                                min="{{ date('Y-m-d') }}"
                                required>
                            @error('booking_date')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <!-- Start Time -->
                        <div class="col-md-3 mb-3">
                            <label for="start_time" class="form-label">
                                <i class="fas fa-clock me-1"></i>Giờ bắt đầu <span class="text-danger">*</span>
                            </label>
                            <select class="form-control @error('start_time') is-invalid @enderror"
                                    id="start_time"
                                    name="start_time"
                                    required>
                                <option value="">Chọn giờ</option>
                                @for($hour = 5; $hour <= 22; $hour++)
                                    @php $time = sprintf('%02d:00', $hour); @endphp
                                    <option value="{{ $time }}"
                                        {{ old('start_time', $preFill['start_time']) === $time ? 'selected' : '' }}>
                                        {{ $time }}
                                    </option>
                                @endfor
                            </select>
                            @error('start_time')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <!-- End Time -->
                        <div class="col-md-3 mb-3">
                            <label for="end_time" class="form-label">
                                <i class="fas fa-clock me-1"></i>Giờ kết thúc <span class="text-danger">*</span>
                            </label>
                            <select class="form-control @error('end_time') is-invalid @enderror"
                                    id="end_time"
                                    name="end_time"
                                    required>
                                <option value="">Chọn giờ</option>
                                @for($hour = 6; $hour <= 23; $hour++)
                                    @php $time = sprintf('%02d:00', $hour); @endphp
                                    <option value="{{ $time }}"
                                        {{ old('end_time', $preFill['end_time']) === $time ? 'selected' : '' }}>
                                        {{ $time }}
                                    </option>
                                @endfor
                            </select>
                            @error('end_time')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>


                        <!-- Price Calculation -->
                        <div class="row mb-3">
                            <div class="col-md-12">
                                <div class="card bg-light">
                                    <div class="card-body">
                                        <div class="row align-items-center">
                                            <div class="col-md-8">
                                                <h6 class="mb-1">Tổng thời gian: <span id="duration">0</span> giờ</h6>
                                                <p class="mb-0 text-muted" id="timeRange">Chưa chọn thời gian</p>
                                            </div>
                                            <div class="col-md-4 text-end">
                                                <h5 class="text-primary mb-0" id="totalPrice">0 VND</h5>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Notes -->
                        <div class="mb-3">
                            <label for="notes" class="form-label">
                                <i class="fas fa-sticky-note me-1"></i>Ghi chú (tùy chọn)
                            </label>
                            <textarea class="form-control @error('notes') is-invalid @enderror"
                                      id="notes" name="notes" rows="3"
                                      placeholder="Nhập ghi chú cho đặt sân...">{{ old('notes') }}</textarea>
                            @error('notes')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <!-- Submit Buttons -->
                        <div class="d-flex justify-content-between">
                            <a href="{{ route('user.courts') }}" class="btn btn-secondary">
                                <i class="fas fa-arrow-left me-1"></i>Quay lại
                            </a>
                            <button type="submit" class="btn btn-primary" id="submitBtn">
                                <i class="fas fa-calendar-check me-1"></i>Đặt sân
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function() {
    const startTimeSelect = document.getElementById('start_time');
    const endTimeSelect = document.getElementById('end_time');
    const durationSpan = document.getElementById('duration');
    const timeRangeP = document.getElementById('timeRange');
    const totalPriceH5 = document.getElementById('totalPrice');
    const submitBtn = document.getElementById('submitBtn');
    const pricePerHour = {{ $court->price_per_hour }};

    function calculatePrice() {
        const startTime = startTimeSelect.value;
        const endTime = endTimeSelect.value;

        if (startTime && endTime) {
            const start = new Date(`2000-01-01T${startTime}:00`);
            const end = new Date(`2000-01-01T${endTime}:00`);

            if (end > start) {
                const hours = (end - start) / (1000 * 60 * 60);
                const totalPrice = hours * pricePerHour;

                durationSpan.textContent = hours;
                timeRangeP.textContent = `${startTime} - ${endTime}`;
                totalPriceH5.textContent = new Intl.NumberFormat('vi-VN').format(totalPrice) + ' VND';

                submitBtn.disabled = false;
                return;
            }
        }

        durationSpan.textContent = '0';
        timeRangeP.textContent = 'Chưa chọn thời gian';
        totalPriceH5.textContent = '0 VND';
        submitBtn.disabled = true;
    }

    // Update end time options based on start time
    function updateEndTimeOptions() {
        const startTime = startTimeSelect.value;
        const endTimeOptions = endTimeSelect.querySelectorAll('option');

        endTimeOptions.forEach(option => {
            if (option.value && option.value <= startTime) {
                option.disabled = true;
            } else {
                option.disabled = false;
            }
        });

        // Reset end time if it's now invalid
        if (endTimeSelect.value && endTimeSelect.value <= startTime) {
            endTimeSelect.value = '';
        }

        calculatePrice();
    }

    startTimeSelect.addEventListener('change', updateEndTimeOptions);
    endTimeSelect.addEventListener('change', calculatePrice);

    // Initial setup for pre-filled values
    if (startTimeSelect.value) {
        updateEndTimeOptions();
    }

    // Force initial calculation after a short delay to ensure DOM is ready
    setTimeout(() => {
        calculatePrice();
    }, 100);

    // Form validation
    document.getElementById('bookingForm').addEventListener('submit', function(e) {
        const startTime = startTimeSelect.value;
        const endTime = endTimeSelect.value;

        if (!startTime || !endTime || endTime <= startTime) {
            e.preventDefault();
            alert('Vui lòng chọn thời gian hợp lệ.');
            return false;
        }
    });
});
</script>
@endpush
