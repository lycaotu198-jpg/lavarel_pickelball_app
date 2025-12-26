@extends('user.layout.app')

@section('title', $court->name)

@section('content')
<div class="container-fluid">
    <div class="row">
        <!-- Court Information -->
        <div class="col-lg-4">
            <div class="card court-card">
                <div class="card-header">
                    <h4 class="card-title mb-0">
                        <i class="fas fa-volleyball-ball me-2"></i>{{ $court->name }}
                    </h4>
                </div>
                <div class="card-body">
                    @if($court->image)
                        <div class="text-center mb-3">
                            <img src="{{ $court->image_url }}" alt="{{ $court->name }}" class="img-fluid rounded" style="max-height: 200px;">
                        </div>
                    @endif

                    <div class="mb-3">
                        <strong><i class="fas fa-map-marker-alt me-2"></i>Địa chỉ:</strong><br>
                        <span class="text-muted">{{ $court->address ?: $court->location }}</span>
                    </div>

                    <div class="mb-3">
                        <strong><i class="fas fa-dollar-sign me-2"></i>Giá thuê:</strong><br>
                        <span class="text-success fw-bold">{{ number_format($court->price_per_hour) }} VND/giờ</span>
                    </div>

                    <div class="mb-3">
                        <strong><i class="fas fa-info-circle me-2"></i>Trạng thái:</strong><br>
                        <span class="badge {{ $court->status_color }}">{{ $court->status_label }}</span>
                    </div>

                    @if($court->status === 'available')
                        <div class="d-grid">
                            <a href="{{ route('user.bookings.create', $court->id) }}" class="btn btn-primary">
                                <i class="fas fa-calendar-plus me-1"></i>Đặt sân
                            </a>
                        </div>
                    @else
                        <div class="alert alert-warning">
                            <i class="fas fa-exclamation-triangle me-2"></i>
                            Sân hiện không khả dụng để đặt.
                        </div>
                    @endif
                </div>
            </div>

            <!-- Quick Stats -->
            <div class="card mt-3">
                <div class="card-header">
                    <h6 class="card-title mb-0">
                        <i class="fas fa-chart-bar me-2"></i>Thống kê nhanh
                    </h6>
                </div>
                <div class="card-body">
                    <div class="row text-center">
                        <div class="col-6">
                            <div class="border-end">
                                <h4 class="text-primary">{{ $bookings->where('status', 'confirmed')->count() }}</h4>
                                <small class="text-muted">Đã đặt</small>
                            </div>
                        </div>
                        <div class="col-6">
                            <h4 class="text-success">{{ 7*24 - $bookings->where('status', 'confirmed')->count() }}</h4>
                            <small class="text-muted">Giờ trống</small>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Schedule -->
        <div class="col-lg-8">
            <div class="card">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h5 class="card-title mb-0">
                        <i class="fas fa-calendar-week me-2"></i>Lịch đặt sân tuần này
                    </h5>
                    <div class="btn-group" role="group">
                        <input type="date" id="scheduleDate" class="form-control form-control-sm" value="{{ now()->startOfWeek()->format('Y-m-d') }}">
                        <button type="button" class="btn btn-outline-secondary btn-sm" id="prevWeek">
                            <i class="fas fa-chevron-left"></i>
                        </button>
                        <button type="button" class="btn btn-outline-secondary btn-sm" id="nextWeek">
                            <i class="fas fa-chevron-right"></i>
                        </button>
                    </div>
                </div>
                <div class="card-body">
                    <div id="schedule-container">
                        <!-- Schedule will be loaded here -->
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Booking Modal -->
<div class="modal fade" id="bookingModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Đặt sân nhanh</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <form id="quickBookingForm">
                    @csrf
                    <input type="hidden" name="court_id" value="{{ $court->id }}">
                    <input type="hidden" name="booking_date" id="modalDate">
                    <input type="hidden" name="start_time" id="modalStartTime">
                    <input type="hidden" name="end_time" id="modalEndTime">

                    <div class="mb-3">
                        <strong>Thông tin đặt sân:</strong>
                        <div id="bookingInfo" class="mt-2 p-3 bg-light rounded">
                            <!-- Booking info will be populated by JavaScript -->
                        </div>
                    </div>

                    <div class="mb-3">
                        <label for="notes" class="form-label">Ghi chú (tùy chọn)</label>
                        <textarea class="form-control" id="notes" name="notes" rows="3" placeholder="Nhập ghi chú..."></textarea>
                    </div>
                </form>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Hủy</button>
                <button type="button" class="btn btn-primary" id="confirmBooking">
                    <i class="fas fa-calendar-check me-1"></i>Đặt sân
                </button>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function() {
    const scheduleDateInput = document.getElementById('scheduleDate');
    const scheduleContainer = document.getElementById('schedule-container');
    const prevWeekBtn = document.getElementById('prevWeek');
    const nextWeekBtn = document.getElementById('nextWeek');

    function loadSchedule() {
        const selectedDate = scheduleDateInput.value;
        scheduleContainer.innerHTML = '<div class="text-center py-4"><div class="spinner-border text-primary"></div><p class="mt-2">Đang tải lịch...</p></div>';

        fetch(`/user/courts/{{ $court->id }}/schedule?date=${selectedDate}`)
            .then(response => response.json())
            .then(data => {
                renderSchedule(data, selectedDate);
            })
            .catch(error => {
                console.error('Error loading schedule:', error);
                scheduleContainer.innerHTML = '<div class="alert alert-danger"><i class="fas fa-exclamation-triangle me-2"></i>Lỗi tải dữ liệu lịch</div>';
            });
    }

    function renderSchedule(bookedSlots, selectedDate) {
        const date = new Date(selectedDate);
        const startOfWeek = new Date(date);
        startOfWeek.setDate(date.getDate() - date.getDay());

        let html = '<div class="table-responsive"><table class="table table-bordered schedule-table">';

        // Header with days
        html += '<thead><tr><th>Giờ</th>';
        for (let i = 0; i < 7; i++) {
            const dayDate = new Date(startOfWeek);
            dayDate.setDate(startOfWeek.getDate() + i);
            const isToday = dayDate.toDateString() === new Date().toDateString();
            const dayClass = isToday ? 'bg-primary text-white' : '';
            html += `<th class="text-center ${dayClass}">${dayDate.toLocaleDateString('vi-VN', {weekday: 'short', day: 'numeric'})}</th>`;
        }
        html += '</tr></thead><tbody>';

        // Time slots
        for (let hour = 6; hour <= 22; hour++) {
            html += `<tr><td class="text-center fw-bold">${String(hour).padStart(2, '0')}:00</td>`;

            for (let day = 0; day < 7; day++) {
                const slotDate = new Date(startOfWeek);
                slotDate.setDate(startOfWeek.getDate() + day);
                const dateString = slotDate.toISOString().split('T')[0];
                const timeString = `${String(hour).padStart(2, '0')}:00`;

                const isBooked = bookedSlots.includes(`${dateString} ${timeString}`);
                const isPast = slotDate < new Date() && slotDate.toDateString() !== new Date().toDateString();
                const canBook = !isBooked && !isPast && '{{ $court->status }}' === 'available';

                let cellClass = 'text-center ';
                let cellContent = '';
                let onclick = '';

                if (isBooked) {
                    cellClass += 'bg-danger text-white';
                    cellContent = '<i class="fas fa-times"></i>';
                } else if (isPast) {
                    cellClass += 'bg-light text-muted';
                    cellContent = '<i class="fas fa-lock"></i>';
                } else if (canBook) {
                    cellClass += 'bg-success text-white slot-available';
                    cellContent = '<i class="fas fa-plus"></i>';
                    onclick = `onclick="bookSlot('${dateString}', '${timeString}')"`;;
                } else {
                    cellClass += 'bg-secondary text-white';
                    cellContent = '<i class="fas fa-ban"></i>';
                }

                html += `<td class="${cellClass}" style="cursor: ${canBook ? 'pointer' : 'not-allowed'};" ${onclick}>${cellContent}</td>`;
            }
            html += '</tr>';
        }

        html += '</tbody></table></div>';
        scheduleContainer.innerHTML = html;
    }

    function bookSlot(date, time) {
        document.getElementById('modalDate').value = date;
        document.getElementById('modalStartTime').value = time;

        // Calculate end time (next hour)
        const startHour = parseInt(time.split(':')[0]);
        const endHour = startHour + 1;
        const endTime = `${String(endHour).padStart(2, '0')}:00`;
        document.getElementById('modalEndTime').value = endTime;

        // Update booking info
        const bookingInfo = document.getElementById('bookingInfo');
        const dateObj = new Date(date);
        const formattedDate = dateObj.toLocaleDateString('vi-VN', {
            weekday: 'long',
            year: 'numeric',
            month: 'long',
            day: 'numeric'
        });

        bookingInfo.innerHTML = `
            <strong>Sân:</strong> {{ $court->name }}<br>
            <strong>Ngày:</strong> ${formattedDate}<br>
            <strong>Thời gian:</strong> ${time} - ${endTime}<br>
            <strong>Giá:</strong> {{ number_format($court->price_per_hour) }} VND
        `;

        const modal = new bootstrap.Modal(document.getElementById('bookingModal'));
        modal.show();
    }

    // Event listeners
    scheduleDateInput.addEventListener('change', loadSchedule);

    prevWeekBtn.addEventListener('click', function() {
        const currentDate = new Date(scheduleDateInput.value);
        currentDate.setDate(currentDate.getDate() - 7);
        scheduleDateInput.value = currentDate.toISOString().split('T')[0];
        loadSchedule();
    });

    nextWeekBtn.addEventListener('click', function() {
        const currentDate = new Date(scheduleDateInput.value);
        currentDate.setDate(currentDate.getDate() + 7);
        scheduleDateInput.value = currentDate.toISOString().split('T')[0];
        loadSchedule();
    });

    // Handle booking confirmation
    document.getElementById('confirmBooking').addEventListener('click', function() {
        const form = document.getElementById('quickBookingForm');
        const formData = new FormData(form);

        fetch('/user/bookings', {
            method: 'POST',
            body: formData,
            headers: {
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.getAttribute('content')
            }
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                const modal = bootstrap.Modal.getInstance(document.getElementById('bookingModal'));
                modal.hide();
                location.reload();
            } else {
                alert('Có lỗi xảy ra: ' + (data.message || 'Không thể đặt sân'));
            }
        })
        .catch(error => {
            console.error('Error:', error);
            alert('Có lỗi xảy ra khi đặt sân');
        });
    });

    // Initial load
    loadSchedule();
});
</script>
@endpush

@push('styles')
<style>
.schedule-table th, .schedule-table td {
    padding: 8px;
    vertical-align: middle;
    font-size: 14px;
}

.schedule-table th {
    background-color: #f8f9fa;
    font-weight: 600;
}

.slot-available:hover {
    background-color: #218838 !important;
    transform: scale(1.1);
    transition: all 0.2s ease;
}

@media (max-width: 768px) {
    .schedule-table {
        font-size: 12px;
    }

    .schedule-table th, .schedule-table td {
        padding: 4px;
    }
}
</style>
@endpush
