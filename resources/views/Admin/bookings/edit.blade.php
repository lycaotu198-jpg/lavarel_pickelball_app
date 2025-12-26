@php
    use Carbon\Carbon;
@endphp
@extends('Admin.layout.app')

@section('title', 'Sửa đặt sân')

@section('content')
<h3 class="mb-4">✏️ Sửa đặt sân</h3>

<form method="POST"
      action="{{ route('admin.bookings.update', $booking) }}">
@csrf
@method('PUT')
{{-- Lỗi trùng giờ --}}
@if ($errors->has('time'))
    <div class="alert alert-danger d-flex align-items-center">
        <strong class="me-2">⚠️</strong>
        {{ $errors->first('time') }}
    </div>
@endif
{{-- KHÁCH HÀNG --}}
<div class="mb-3">
    <label>Khách hàng</label>
    <select name="user_id" class="form-control" required>
        @foreach($users as $user)
            <option value="{{ $user->id }}"
                {{ $booking->user_id == $user->id ? 'selected' : '' }}>
                {{ $user->name }}
            </option>
        @endforeach
    </select>
</div>

{{-- SÂN --}}
<div class="mb-3">
    <label>Sân</label>
    <select name="court_id" id="court_id" class="form-control" required>
        @foreach($courts as $court)
            <option value="{{ $court->id }}"
                data-price="{{ $court->price_per_hour }}"
                {{ $booking->court_id == $court->id ? 'selected' : '' }}>
                {{ $court->name }} ({{ number_format($court->price_per_hour) }}đ/giờ)
            </option>
        @endforeach
    </select>
</div>

{{-- NGÀY --}}
<div class="mb-3">
    <label>Ngày</label>
    <input type="date"
           name="booking_date"
           id="booking_date"
           value="{{ $booking->booking_date }}"
           class="form-control"
           required>
</div>

{{-- CHỌN THỜI GIAN --}}
<div class="mb-4">
    <h6 class="mb-3">
        <i class="fas fa-clock me-1"></i>Chọn khung giờ ({{ $booking->booking_date }})
    </h6>
    <div id="selectionInstructions" class="alert alert-info" style="display: none;">
        <small><i class="fas fa-mouse-pointer me-1"></i>Nhấp vào thời gian kết thúc để hoàn thành lựa chọn.</small>
    </div>
    <div id="timeSlots" class="row g-2">
        @php
            $startHour = $booking->start_time ? Carbon::parse($booking->start_time)->hour : 6;
            $endHour = $booking->end_time ? Carbon::parse($booking->end_time)->hour : 7;
            $currentSlots = [];
            for ($h = $startHour; $h < $endHour; $h++) {
                $currentSlots[] = sprintf('%02d:00', $h);
            }
        @endphp
        @for($hour = 6; $hour < 22; $hour++)
            <div class="col-6 col-md-3 col-lg-2">
                <button type="button"
                        class="btn time-slot w-100 {{ in_array(sprintf('%02d:00', $hour), $currentSlots) ? 'btn-primary selected' : 'btn-success available' }}"
                        data-time="{{ sprintf('%02d:00', $hour) }}">
                    {{ sprintf('%02d:00', $hour) }}
                </button>
            </div>
        @endfor
    </div>
    <div class="mt-2">
        <small class="text-muted">
            <i class="fas fa-circle text-success me-1"></i>Còn trống
            <i class="fas fa-circle text-danger ms-3 me-1"></i>Đã đặt
            <i class="fas fa-circle text-primary ms-3 me-1"></i>Đang chọn
            <i class="fas fa-circle text-warning ms-3 me-1"></i>Điểm bắt đầu
        </small>
    </div>
</div>

{{-- THỜI GIAN ĐÃ CHỌN --}}
<div class="row mb-3" id="selectedTimeDisplay">
    <div class="col-md-12">
        <div class="card bg-primary text-white">
            <div class="card-body">
                <h6 class="card-title mb-1">
                    <i class="fas fa-check-circle me-1"></i>Thời gian đã chọn
                </h6>
                <p class="mb-0" id="selectedTimeText">{{ $booking->start_time && $booking->end_time ? $booking->start_time . ' - ' . $booking->end_time : 'Chưa chọn thời gian' }}</p>
            </div>
        </div>
    </div>
</div>

{{-- GIÁ --}}
<div class="mb-3">
    <label>Giá (VND)</label>
    <input type="number"
           name="total_price"
           id="total_price"
           value="{{ $booking->total_price }}"
           class="form-control"
           readonly>
</div>

{{-- ẨN THỜI GIAN INPUT --}}
<input type="hidden" name="start_time" id="start_time_input" value="{{ $booking->start_time ? substr($booking->start_time, 0, 5) : '' }}">
<input type="hidden" name="end_time" id="end_time_input" value="{{ $booking->end_time ? substr($booking->end_time, 0, 5) : '' }}">

{{-- NÚT --}}
<div class="mt-4 d-flex justify-content-end gap-2">
    <a href="{{ route('admin.bookings.index') }}" class="btn btn-secondary">
        ⬅ Quay về
    </a>
    <button class="btn btn-success">
        💾 Cập nhật
    </button>
</div>

</form>

{{-- STYLE --}}
<style>
.time-grid {
    display: grid;
    grid-template-columns: repeat(auto-fill, minmax(90px, 1fr));
    gap: 10px;
}
.time-slot.busy {
    background: #dc3545;
    color: white;
    cursor: not-allowed;
    border-color: #dc3545;
}

.time-slot.busy:hover {
    background: #dc3545;
}


.time-slot {
    border: 2px solid #dee2e6;
    border-radius: 8px;
    padding: 10px 0;
    text-align: center;
    cursor: pointer;
    font-weight: 500;
    background: #f8f9fa;
    transition: 0.2s;
}

.time-slot:hover {
    background: #e7f1ff;
    border-color: #0d6efd;
}

.time-slot.selected {
    background: #0d6efd;
    color: white;
    border-color: #0d6efd;
}
</style>

{{-- SCRIPT --}}
<script>
const courtSelect = document.getElementById('court_id');
const dateInput = document.getElementById('booking_date');
const timeSlotsContainer = document.getElementById('timeSlots');
const selectedTimeDisplay = document.getElementById('selectedTimeDisplay');
const selectedTimeText = document.getElementById('selectedTimeText');
const startTimeInput = document.getElementById('start_time_input');
const endTimeInput = document.getElementById('end_time_input');
const priceInput = document.getElementById('total_price');

let selectedSlots = @json($currentSlots);
let isSelecting = false;
let startSlot = null;

// Load time slots for selected date and court
function loadTimeSlots() {
    const courtId = courtSelect.value;
    const date = dateInput.value;

    fetch(`{{ route('admin.bookings.busy-times') }}?court_id=${courtId}&date=${date}&booking_id={{ $booking->id }}`)
        .then(response => response.json())
        .then(data => {
            updateTimeSlots(data || []);
        })
        .catch(error => {
            console.error('Error loading time slots:', error);
        });
}

// Update time slots display
function updateTimeSlots(bookedSlots) {
    const timeSlotButtons = document.querySelectorAll('.time-slot');
    const selectionInstructions = document.getElementById('selectionInstructions');

    timeSlotButtons.forEach(button => {
        const time = button.dataset.time;
        const isBooked = bookedSlots.includes(time);
        const isSelected = selectedSlots.includes(time);
        const isStartSlot = isSelecting && time === startSlot;

        button.className = 'btn time-slot w-100';
        if (isBooked) {
            button.classList.add('btn-danger', 'booked');
            button.disabled = true;
        } else if (isStartSlot) {
            button.classList.add('btn-warning', 'selection-start');
            button.disabled = false;
        } else if (isSelected) {
            button.classList.add('btn-primary', 'selected');
            button.disabled = false;
        } else {
            button.classList.add('btn-success', 'available');
            button.disabled = false;
        }
    });

    // Show/hide selection instructions
    if (isSelecting) {
        selectionInstructions.style.display = 'block';
    } else {
        selectionInstructions.style.display = 'none';
    }

    updateSelectedTimeDisplay();
    calculatePrice();
}

// Update selected time display
function updateSelectedTimeDisplay() {
    if (selectedSlots.length === 0) {
        selectedTimeDisplay.style.display = 'none';
        return;
    }

    selectedSlots.sort();
    const startTime = selectedSlots[0];
    const endTime = selectedSlots[selectedSlots.length - 1];
    const nextHour = new Date(`2000-01-01T${endTime}:00`);
    nextHour.setHours(nextHour.getHours() + 1);
    const endTimeFormatted = nextHour.toTimeString().slice(0, 5);

    selectedTimeText.textContent = `${startTime} - ${endTimeFormatted}`;
    selectedTimeDisplay.style.display = 'block';

    startTimeInput.value = startTime;
    endTimeInput.value = endTimeFormatted;
}

// Calculate and display price
function calculatePrice() {
    if (selectedSlots.length === 0) {
        priceInput.value = '';
        return;
    }

    const pricePerHour = parseFloat(courtSelect.options[courtSelect.selectedIndex].dataset.price);
    const hours = selectedSlots.length;
    const totalPrice = hours * pricePerHour;

    priceInput.value = Math.round(totalPrice);
}

// Handle time slot click
function handleTimeSlotClick(event) {
    const button = event.target.closest('.time-slot');
    if (!button || button.disabled || button.classList.contains('booked')) return;

    const time = button.dataset.time;

    // Only allow selection on available slots
    if (button.classList.contains('booked')) return;

    if (isSelecting) {
        // Finish selection - select range from startSlot to current time
        const allSlots = Array.from(document.querySelectorAll('.time-slot'));
        const startIndex = allSlots.findIndex(btn => btn.dataset.time === startSlot);
        const endIndex = allSlots.findIndex(btn => btn.dataset.time === time);

        if (startIndex !== -1 && endIndex !== -1) {
            // Ensure start is before end
            const minIndex = Math.min(startIndex, endIndex);
            const maxIndex = Math.max(startIndex, endIndex);

            selectedSlots = [];
            for (let i = minIndex; i <= maxIndex; i++) {
                const slotButton = allSlots[i];
                const slotTime = slotButton.dataset.time;

                // Only select if slot is available (not booked)
                if (!slotButton.classList.contains('booked')) {
                    selectedSlots.push(slotTime);
                }
            }
        }

        isSelecting = false;
        startSlot = null;
    } else {
        // Start new selection - only on available slots
        if (!button.classList.contains('booked')) {
            selectedSlots = [time];
            isSelecting = true;
            startSlot = time;
        }
    }

    updateTimeSlots([]);
}

// Date change handler
dateInput.addEventListener('change', function() {
    selectedSlots = [];
    isSelecting = false;
    startSlot = null;
    loadTimeSlots();
});

// Court change handler
courtSelect.addEventListener('change', function() {
    selectedSlots = [];
    isSelecting = false;
    startSlot = null;
    loadTimeSlots();
});

// Time slot click handler
timeSlotsContainer.addEventListener('click', handleTimeSlotClick);

// Form validation
document.querySelector('form').addEventListener('submit', function(e) {
    if (selectedSlots.length === 0) {
        e.preventDefault();
        alert('Vui lòng chọn thời gian đặt sân.');
        return false;
    }
});

// Initial load
loadTimeSlots();
</script>

@endsection
