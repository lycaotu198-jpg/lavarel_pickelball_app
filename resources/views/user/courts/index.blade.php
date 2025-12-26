@extends('user.layout.app')
@section('title', 'Đặt sân chuyên nghiệp')

@section('content')
<div class="container-fluid py-4">
    <!-- Header & Legend (Giữ nguyên như bản trước) -->
    <div class="row mb-4 align-items-center">
        <div class="col-md-6">
            <h4 class="fw-bold text-dark mb-1">Hệ thống đặt sân</h4>
            <p class="text-muted mb-0">Lưu ý: Chỉ được chọn các khung giờ liên tiếp trên cùng một sân</p>
        </div>
        <div class="col-md-3 ms-auto">
            <div class="input-group shadow-sm">
                <span class="input-group-text bg-white border-end-0"><i class="fas fa-calendar-alt text-primary"></i></span>
                <input type="date" id="date" class="form-control border-start-0 ps-0 fw-bold"
                       value="{{ date('Y-m-d') }}" min="{{ date('Y-m-d') }}">
            </div>
        </div>
    </div>

    <!-- Main Timetable Card -->
    <div class="card border-0 shadow-sm overflow-hidden">
        <div class="timetable-wrapper">
            <table class="table mb-0">
                <thead>
                    <tr id="timeHeader">
                        <th class="sticky-col sticky-header">Sân / Giờ</th>
                    </tr>
                </thead>
                <tbody id="timetableBody"></tbody>
            </table>
        </div>
    </div>
</div>

<!-- Floating Selection Bar -->
<div id="booking-actions" class="selection-bar shadow-lg" style="display:none">
    <div class="container-fluid d-flex align-items-center justify-content-between">
        <div class="d-flex align-items-center">
            <div class="selection-icon d-none d-md-flex">
                <i class="fas fa-clock"></i>
            </div>
            <div>
                <h6 class="mb-0 fw-bold text-white"><span id="display-court-name"></span></h6>
                <small class="text-white-50" id="selected-summary"></small>
            </div>
        </div>
        <div class="d-flex gap-2">
            <button id="clearSelectionBtn" class="btn btn-outline-light btn-sm px-3">Xóa chọn</button>
            <button id="bookSelectedBtn" class="btn btn-white text-primary fw-bold px-4">
                ĐẶT NGAY <i class="fas fa-arrow-right ms-2"></i>
            </button>
        </div>
    </div>
</div>

<!-- Toast Notification (Thông báo nhẹ) -->
<div id="toast-container" class="position-fixed top-0 end-0 p-3" style="z-index: 1060"></div>

<style>
    /* CSS cũ giữ nguyên, thêm CSS cho Toast */
    :root {
        --primary-color: #4f46e5;
        --bg-available: #ecfdf5;
        --text-available: #059669;
        --bg-booked: #fef2f2;
        --text-booked: #dc2626;
        --bg-past: #f3f4f6;
        --text-past: #9ca3af;
        --bg-selected: #4f46e5;
        --text-selected: #ffffff;
    }

    .timetable-wrapper { max-height: 65vh; overflow: auto; position: relative; border-radius: 8px; }
    .table { border-collapse: separate; border-spacing: 0; table-layout: fixed; min-width: 1400px; }
    .table thead th { position: sticky; top: 0; background: #f8fafc; color: #64748b; z-index: 10; font-weight: 600; font-size: 0.85rem; padding: 1rem 0.5rem; border-bottom: 2px solid #e2e8f0; text-align: center; }
    .sticky-col { position: sticky; left: 0; z-index: 20; background: #fff !important; width: 180px; min-width: 180px; padding-left: 1.5rem !important; text-align: left !important; border-right: 1px solid #e2e8f0; box-shadow: 4px 0 8px rgba(0,0,0,0.02); }
    .sticky-header { z-index: 30 !important; background: #f8fafc !important; }

    .slot { height: 55px; transition: all 0.2s; border: 0.5px solid #f1f5f9; font-size: 0.85rem; cursor: pointer; }
    .slot-available { color: var(--text-available); }
    .slot-available:hover { background-color: var(--bg-available); }
    .slot-booked { background-color: var(--bg-booked); color: var(--text-booked); cursor: not-allowed; }
    .slot-past { background-color: var(--bg-past); color: var(--text-past); cursor: not-allowed; }
    .slot-selected { background-color: var(--bg-selected) !important; color: white !important; font-weight: bold; }

    .selection-bar { position: fixed; bottom: 20px; left: 50%; transform: translateX(-50%); width: 90%; max-width: 800px; background: var(--primary-color); border-radius: 16px; padding: 1rem 1.5rem; z-index: 1000; display: flex; align-items: center; animation: slideUp 0.4s cubic-bezier(0.175, 0.885, 0.32, 1.275); }
    @keyframes slideUp { from { bottom: -100px; opacity: 0; } to { bottom: 20px; opacity: 1; } }
    .btn-white { background: white; color: var(--primary-color); border: none; }

    .toast-msg { background: #333; color: white; padding: 12px 20px; border-radius: 8px; margin-bottom: 10px; box-shadow: 0 4px 12px rgba(0,0,0,0.15); display: none; }
</style>

<script>
document.addEventListener('DOMContentLoaded', () => {
    const dateInput = document.getElementById('date');
    const body = document.getElementById('timetableBody');
    const header = document.getElementById('timeHeader');
    const actionBar = document.getElementById('booking-actions');

    let selectedCourtId = null;
    let selectedTimes = []; // Lưu mảng các string giờ ["08:00", "09:00"]
    let timeSlotsArray = []; // Lưu danh sách tất cả các khung giờ từ server

    function showToast(msg) {
        const toast = document.createElement('div');
        toast.className = 'toast-msg animate__animated animate__fadeInRight';
        toast.innerHTML = `<i class="fas fa-info-circle me-2"></i> ${msg}`;
        document.getElementById('toast-container').appendChild(toast);
        toast.style.display = 'block';
        setTimeout(() => {
            toast.remove();
        }, 3000);
    }

    function loadTimetable() {
        body.innerHTML = '<tr><td colspan="20" class="text-center py-5">Đang tải...</td></tr>';
        fetch(`/user/courts/timetable?date=${dateInput.value}`)
            .then(res => res.json())
            .then(data => {
                timeSlotsArray = data.timeSlots;
                renderTable(data);
            });
    }

    function renderTable(data) {
        const { courts, timeSlots, bookings, pastSlots } = data;
        let headHtml = '<th class="sticky-col sticky-header">Sân / Giờ</th>';
        timeSlots.forEach(t => headHtml += `<th>${t}</th>`);
        header.innerHTML = headHtml;

         let bodyHtml = '';
    courts.forEach(court => {
        bodyHtml += `<tr><td class="sticky-col fw-bold">${court.name}</td>`;
        timeSlots.forEach(slot => {
            const isBooked = bookings[court.id]?.includes(slot);
            const isPast = pastSlots.includes(slot); // Controller đã tính toán slot này quá khứ chưa

            let state = 'slot-available';
            let icon = '+';

            if (isBooked) {
                state = 'slot-booked';
                icon = '<i class="fas fa-times"></i>';
            } else if (isPast) {
                state = 'slot-past';
                icon = '<i class="fas fa-clock-rotate-left opacity-50"></i>'; // Icon giờ đã qua
            }

            bodyHtml += `
                <td class="slot text-center ${state}"
                    data-court-id="${court.id}"
                    data-court-name="${court.name}"
                    data-time="${slot}"
                    data-booked="${isBooked}"
                    data-past="${isPast}"
                    onclick="handleSelect(this)">
                    ${icon}
                </td>`;
        });
        bodyHtml += '</tr>';
    });
    body.innerHTML = bodyHtml;
    }

    window.handleSelect = function(cell) {
        if (cell.dataset.booked === 'true' || cell.dataset.past === 'true') return;

        const courtId = cell.dataset.courtId;
        const courtName = cell.dataset.courtName;
        const time = cell.dataset.time;

        // 1. Kiểm tra nếu chọn sân khác
        if (selectedCourtId && selectedCourtId !== courtId) {
            if(confirm("Bạn muốn đổi sang đặt sân này? Các giờ đã chọn ở sân cũ sẽ bị hủy.")) {
                clearAllSelection();
            } else {
                return;
            }
        }

        selectedCourtId = courtId;
        document.getElementById('display-court-name').innerText = courtName;

        // 2. Logic chọn/hủy giờ
        if (selectedTimes.includes(time)) {
            // Nếu hủy chọn: Để đảm bảo tính liên tục, nếu hủy 1 ô ở giữa, ta xóa hết chọn lại từ đầu cho chắc chắn
            // Hoặc có thể logic hơn là chỉ cho phép hủy 2 đầu. Ở đây ta chọn xóa hết để tránh bug logic.
            clearAllSelection();
        } else {
            // Nếu thêm chọn: Kiểm tra tính liên tục
            if (selectedTimes.length > 0) {
                if (!isConsecutive(time)) {
                    showToast("Vui lòng chọn khung giờ liền kề với giờ đã chọn!");
                    return;
                }
            }
            selectedTimes.push(time);
            // Sắp xếp lại mảng giờ sau mỗi lần chọn
            selectedTimes.sort((a, b) => timeSlotsArray.indexOf(a) - timeSlotsArray.indexOf(b));
            cell.classList.add('slot-selected');
            cell.innerHTML = '<i class="fas fa-check"></i>';
        }

        updateUI();
    };

    function isConsecutive(newTime) {
        // Lấy vị trí index của giờ vừa click trong mảng tổng
        const newIdx = timeSlotsArray.indexOf(newTime);

        // Tìm index nhỏ nhất và lớn nhất trong số các ô đã chọn
        const selectedIndices = selectedTimes.map(t => timeSlotsArray.indexOf(t));
        const minIdx = Math.min(...selectedIndices);
        const maxIdx = Math.max(...selectedIndices);

        // Giờ mới chọn phải nằm ngay trước min hoặc ngay sau max
        return (newIdx === minIdx - 1 || newIdx === maxIdx + 1);
    }

    function clearAllSelection() {
        selectedCourtId = null;
        selectedTimes = [];
        document.querySelectorAll('.slot-selected').forEach(c => {
            c.classList.remove('slot-selected');
            c.innerHTML = '+';
        });
        updateUI();
    }

    function updateUI() {
        const total = selectedTimes.length;
        actionBar.style.display = total > 0 ? 'flex' : 'none';
        if (total > 0) {
            const start = selectedTimes[0];
            const end = selectedTimes[total-1];
            document.getElementById('selected-summary').innerText = `Từ ${start} đến ${calculateEndTime(end)} (${total} tiếng)`;
        }
    }

    function calculateEndTime(lastSlot) {
        const hour = parseInt(lastSlot.split(':')[0], 10);
        return String(hour + 1).padStart(2, '0') + ':00';
    }

    document.getElementById('clearSelectionBtn').onclick = clearAllSelection;

  document.getElementById('bookSelectedBtn').onclick = () => {
    if (!selectedCourtId || selectedTimes.length === 0) return;

    const startTime = selectedTimes[0];
    const endTime = calculateEndTime(selectedTimes[selectedTimes.length - 1]);
        const selectedDate = document.getElementById('date').value; // Lấy trực tiếp từ input ngày

        console.log("Redirecting to date:", selectedDate); // Kiểm tra xem có phải 2025-12-25 không

        location.href = `/user/courts/${selectedCourtId}/book?date=${selectedDate}&start_time=${startTime}&end_time=${endTime}`;
    };

    dateInput.addEventListener('change', () => {
        clearAllSelection();
        loadTimetable();
    });

    loadTimetable();
});
</script>
@endsection
