@extends('Admin.layout.app')

@section('title', 'Thêm đặt sân')

@section('content')
<h3 class="mb-4">➕ Thêm đặt sân</h3>

{{-- LỖI TRÙNG GIỜ --}}
@if ($errors->has('time'))
    <div class="alert alert-danger">
        ⚠️ {{ $errors->first('time') }}
    </div>
@endif

<form method="POST" action="{{ route('admin.bookings.store') }}">
@csrf

{{-- KHÁCH HÀNG --}}
<div class="mb-3">
    <label class="form-label">Khách hàng</label>
    <select name="user_id" class="form-control" required>
        @foreach($users as $user)
            <option value="{{ $user->id }}">{{ $user->name }}</option>
        @endforeach
    </select>
</div>

{{-- SÂN --}}
<div class="mb-3">
    <label class="form-label">Sân</label>
    <select name="court_id" id="court_id" class="form-control" required>
        @foreach($courts as $court)
            <option value="{{ $court->id }}" data-price="{{ $court->price_per_hour }}">
                {{ $court->name }} ({{ number_format($court->price_per_hour) }}đ/giờ)
            </option>
        @endforeach
    </select>
</div>

{{-- NGÀY --}}
<div class="mb-3">
    <label class="form-label">Ngày</label>
    <input type="date" id="booking_date" name="booking_date" class="form-control" value="{{ date('Y-m-d') }}" min="{{ date('Y-m-d') }}" required>
</div>

{{-- CHỌN THỜI GIAN --}}
<div class="mb-4">
    <h6 class="mb-3 fw-bold"><i class="fas fa-clock me-1"></i>Bảng khung giờ</h6>

    <div id="selectionInstructions" class="alert alert-info py-2" style="display: none;">
        <small><i class="fas fa-mouse-pointer me-1"></i>Chọn giờ kết thúc để hoàn thành (Hoặc nhấn lại giờ bắt đầu để hủy)</small>
    </div>

    <div id="timeSlots" class="row g-2">
        @for($hour = 6; $hour < 22; $hour++)
            @php $t = sprintf('%02d:00', $hour); @endphp
            <div class="col-4 col-md-3 col-lg-2">
                <button type="button" class="btn time-slot w-100 available" data-time="{{ $t }}">
                    {{ $t }}
                </button>
            </div>
        @endfor
    </div>

    <div class="mt-3 p-2 border rounded bg-light">
        <small class="text-muted">
            <span class="badge bg-success me-1"> </span> Trống
            <span class="badge bg-danger ms-3 me-1"> </span> Đã có người đặt (Đỏ)
            <span class="badge bg-warning ms-3 me-1 text-dark"> </span> Đang chọn
        </small>
    </div>
</div>

{{-- THÔNG TIN CHỐT --}}
<div class="card bg-primary text-white mb-3" id="selectedTimeDisplay" style="display: none;">
    <div class="card-body py-2 d-flex justify-content-between align-items-center">
        <div>
            <i class="fas fa-check-circle me-1"></i>
            <strong>Đã chọn:</strong> <span id="selectedTimeText"></span>
        </div>
        <div class="text-end">
            <strong>Tổng:</strong> <input type="text" id="total_price_display" style="background:transparent; border:none; color:white; width:80px; text-align:right; font-weight:bold;" readonly> VND
        </div>
    </div>
</div>

<input type="hidden" name="total_price" id="total_price">
<input type="hidden" name="start_time" id="start_time_input">
<input type="hidden" name="end_time" id="end_time_input">

<div class="mt-4 d-flex justify-content-end gap-2">
    <a href="{{ route('admin.bookings.index') }}" class="btn btn-secondary">Quay về</a>
    <button type="submit" class="btn btn-success px-4 shadow">💾 Lưu đặt sân</button>
</div>

</form>

<style>
    .time-slot {
        font-weight: 600;
        border-width: 2px;
        transition: all 0.2s ease;
        padding: 10px 5px;
        border-radius: 8px;
    }
    /* Trống */
    .time-slot.available {
        background-color: #fff;
        color: #198754;
        border-color: #198754;
    }
    .time-slot.available:hover {
        background-color: #198754;
        color: #fff;
    }
    /* ĐÃ ĐẶT (MÀU ĐỎ) */
    .time-slot.booked {
        background-color: #dc3545 !important;
        border-color: #dc3545 !important;
        color: #fff !important;
        cursor: not-allowed;
        opacity: 0.9;
        pointer-events: none; /* Không cho click */
    }
    /* Đang chọn */
    .time-slot.selected {
        background-color: #0d6efd !important;
        border-color: #0d6efd !important;
        color: #fff !important;
    }
    .time-slot.selection-start {
        background-color: #ffc107 !important;
        border-color: #ffc107 !important;
        color: #000 !important;
        animation: pulse 1s infinite;
    }
    @keyframes pulse {
        0% { opacity: 1; }
        50% { opacity: 0.7; }
        100% { opacity: 1; }
    }
</style>

<script>
const courtEl = document.getElementById('court_id');
const dateEl = document.getElementById('booking_date');
const timeSlotsContainer = document.getElementById('timeSlots');
const selectedTimeDisplay = document.getElementById('selectedTimeDisplay');
const selectedTimeText = document.getElementById('selectedTimeText');
const startTimeInput = document.getElementById('start_time_input');
const endTimeInput = document.getElementById('end_time_input');
const priceInput = document.getElementById('total_price');
const priceDisplay = document.getElementById('total_price_display');

let selectedSlots = [];
let isSelecting = false;
let startSlot = null;
let currentBookedSlots = [];

function loadTimeSlots() {
    const courtId = courtEl.value;
    const date = dateEl.value;

    // Xóa lựa chọn cũ khi đổi sân/ngày
    selectedSlots = [];
    isSelecting = false;
    startSlot = null;

    fetch(`{{ route('admin.bookings.busy-times') }}?court_id=${courtId}&date=${date}`)
        .then(res => res.json())
        .then(data => {
            // Chuẩn hóa dữ liệu về định dạng HH:mm (vì MySQL có thể trả về HH:mm:ss)
            currentBookedSlots = (data || []).map(t => t.substring(0, 5));
            updateTimeSlots();
        });
}

function updateTimeSlots() {
    const timeSlotButtons = document.querySelectorAll('.time-slot');

    timeSlotButtons.forEach(button => {
        const time = button.dataset.time;
        const isBooked = currentBookedSlots.includes(time);
        const isSelected = selectedSlots.includes(time);
        const isStart = isSelecting && time === startSlot;

        button.className = 'btn time-slot w-100';
        button.disabled = false;

        if (isBooked) {
            button.classList.add('booked');
            button.innerHTML = `<i class="fas fa-times me-1"></i>${time}`;
        } else if (isStart) {
            button.classList.add('selection-start');
            button.innerHTML = `<i class="fas fa-arrow-right me-1"></i>${time}`;
        } else if (isSelected) {
            button.classList.add('selected');
            button.innerHTML = `<i class="fas fa-check me-1"></i>${time}`;
        } else {
            button.classList.add('available');
            button.innerHTML = time;
        }
    });

    document.getElementById('selectionInstructions').style.display = isSelecting ? 'block' : 'none';
    updateDisplay();
}

function handleTimeSlotClick(event) {
    const button = event.target.closest('.time-slot');
    if (!button || button.classList.contains('booked')) return;

    const time = button.dataset.time;

    if (!isSelecting) {
        selectedSlots = [time];
        startSlot = time;
        isSelecting = true;
    } else {
        if (time === startSlot) { // Nhấn lại chính nó để hủy
            selectedSlots = [];
            isSelecting = false;
            startSlot = null;
        } else {
            const allButtons = Array.from(document.querySelectorAll('.time-slot'));
            const idx1 = allButtons.findIndex(b => b.dataset.time === startSlot);
            const idx2 = allButtons.findIndex(b => b.dataset.time === time);

            const startIdx = Math.min(idx1, idx2);
            const endIdx = Math.max(idx1, idx2);

            let tempRange = [];
            let blocked = false;

            for (let i = startIdx; i <= endIdx; i++) {
                const t = allButtons[i].dataset.time;
                if (currentBookedSlots.includes(t)) {
                    blocked = true;
                    break;
                }
                tempRange.push(t);
            }

            if (blocked) {
                alert('Khung giờ này chứa giờ đã được đặt! Vui lòng chọn lại.');
                selectedSlots = [];
            } else {
                selectedSlots = tempRange;
            }
            isSelecting = false;
            startSlot = null;
        }
    }
    updateTimeSlots();
}

function updateDisplay() {
    if (selectedSlots.length === 0) {
        selectedTimeDisplay.style.display = 'none';
        return;
    }

    selectedSlots.sort();
    const start = selectedSlots[0];
    const last = selectedSlots[selectedSlots.length - 1];

    // Tính End Time: Giờ của slot cuối + 1
    const endHour = parseInt(last.split(':')[0]) + 1;
    const end = String(endHour).padStart(2, '0') + ':00';

    selectedTimeText.innerText = `${start} - ${end}`;
    startTimeInput.value = start;
    endTimeInput.value = end;
    selectedTimeDisplay.style.display = 'block';

    const pricePerHour = parseFloat(courtEl.options[courtEl.selectedIndex].dataset.price);
    const total = selectedSlots.length * pricePerHour;
    priceInput.value = total;
    priceDisplay.value = new Intl.NumberFormat('vi-VN').format(total);
}

// Event Listeners
timeSlotsContainer.addEventListener('click', handleTimeSlotClick);
dateEl.addEventListener('change', loadTimeSlots);
courtEl.addEventListener('change', loadTimeSlots);

// Init
loadTimeSlots();
</script>
@endsection
