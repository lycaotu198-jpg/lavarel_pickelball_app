<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rule;
use App\Models\Court;
use App\Models\Booking;
use App\Models\User;
use App\Models\Payment;
use Carbon\Carbon;

class UserController extends Controller
{
    /**
     * Hiển thị dashboard của user
     */
    public function dashboard()
    {
        $user = Auth::user();

        // Lấy thông tin thống kê của user
        $totalBookings = Booking::where('user_id', $user->id)->count();
        $upcomingBookings = Booking::where('user_id', $user->id)
            ->where('booking_date', '>=', now()->toDateString())
            ->where('status', 'confirmed')
            ->count();
        $totalSpent = Booking::where('user_id', $user->id)
            ->where('status', 'confirmed')
            ->sum('total_price');

        // Lấy các booking sắp tới
        $recentBookings = Booking::with(['court'])
            ->where('user_id', $user->id)
            ->where('booking_date', '>=', now()->toDateString())
            ->orderBy('booking_date')
            ->orderBy('start_time')
            ->limit(5)
            ->get();

        return view('user.dashboard', compact(
            'totalBookings',
            'upcomingBookings',
            'totalSpent',
            'recentBookings'
        ));
    }

    /**
     * Hiển thị danh sách sân
     */
    public function courts()
    {
        $courts = Court::where('status', 'available')
            ->orderBy('name')
            ->get();

        return view('user.courts.index', compact('courts'));
    }

    /**
     * Hiển thị chi tiết sân
     */
    public function showCourt($id)
    {
        $court = Court::findOrFail($id);

        // Lấy các booking trong tuần này cho sân này
        $startOfWeek = now()->startOfWeek();
        $endOfWeek = now()->endOfWeek();

        $bookings = Booking::where('court_id', $id)
            ->whereBetween('booking_date', [$startOfWeek->toDateString(), $endOfWeek->toDateString()])
            ->where('status', 'confirmed')
            ->get();

        return view('user.courts.show', compact('court', 'bookings'));
    }

    /**
     * API: Lấy dữ liệu booking cho calendar
     */
    public function getCourtSchedule(Request $request, $courtId)
    {
        $date = $request->get('date', now()->toDateString());

        $bookings = Booking::where('court_id', $courtId)
            ->where('booking_date', $date)
            ->where('status', 'confirmed')
            ->get(['start_time', 'end_time']);

        $bookedSlots = [];
        foreach ($bookings as $booking) {
            $start = Carbon::parse($booking->start_time);
            $end = Carbon::parse($booking->end_time);

            while ($start < $end) {
                $bookedSlots[] = $start->format('H:i');
                $start->addHour();
            }
        }

        return response()->json([
            'booked_slots' => array_unique($bookedSlots)
        ]);
    }

    /**
     * Hiển thị profile của user
     */
    public function profile()
    {
        $user = Auth::user();
        return view('user.profile', compact('user'));
    }

    /**
     * Cập nhật profile của user
     */
    public function updateProfile(Request $request)
    {
        $user = Auth::user();

        $request->validate([
            'name' => 'required|string|max:255',
            'email' => ['required', 'string', 'email', 'max:255', Rule::unique('users')->ignore($user->id)],
            'phone' => 'nullable|string|max:20',
            'address' => 'nullable|string|max:500',
            'current_password' => 'nullable|required_with:new_password',
            'new_password' => 'nullable|min:8|confirmed',
        ]);

        $updateData = [
            'name' => $request->name,
            'email' => $request->email,
            'phone' => $request->phone,
            'address' => $request->address,
        ];

        // Nếu có mật khẩu mới
        if ($request->filled('new_password')) {
            if (!Hash::check($request->current_password, $user->password)) {
                return back()->withErrors(['current_password' => 'Mật khẩu hiện tại không đúng']);
            }
            $updateData['password'] = Hash::make($request->new_password);
        }

        $user->update($updateData);

        return back()->with('success', 'Cập nhật thông tin thành công!');
    }

    /**
     * Hiển thị form đặt sân
     */


public function createBooking(Request $request, $courtId)
{
    $court = Court::findOrFail($courtId);

    // 1️⃣ Lấy & chuẩn hóa ngày
    $date = $request->query('date');

    if (!$date || !Carbon::hasFormat($date, 'Y-m-d')) {
        $date = now()->toDateString();
    }

    // 2️⃣ Lấy giờ từ query (KHÔNG gọi request() rải rác)
    $startTime = $request->query('start_time');
    $endTime   = $request->query('end_time');

    // 3️⃣ Prefill chuẩn
    $preFill = [
        'date'       => $date,
        'start_time' => $startTime,
        'end_time'   => $endTime,
    ];

    // 4️⃣ Lấy booking đúng NGÀY đã chọn
    $bookings = Booking::where('court_id', $courtId)
        ->where('booking_date', $date)
        ->where('status', 'confirmed')
        ->get(['start_time', 'end_time']);

    // 5️⃣ Build booked slots theo giờ
    $bookedSlots = [];

    foreach ($bookings as $booking) {
        $start = Carbon::createFromFormat('H:i:s', $booking->start_time);
        $end   = Carbon::createFromFormat('H:i:s', $booking->end_time);

        while ($start < $end) {
            $bookedSlots[] = $start->format('H:i');
            $start->addHour();
        }
    }

    return view('user.bookings.create', compact(
        'court',
        'preFill',
        'bookedSlots'
    ));
}


    /**
     * Lưu đặt sân mới
     */
   public function storeBooking(Request $request)
{
    $request->validate([
        'court_id' => 'required|exists:courts,id',
        'booking_date' => 'required|date|after_or_equal:today',
        'start_time' => 'required|date_format:H:i',
        'end_time' => 'required|date_format:H:i|after:start_time',
        'notes' => 'nullable|string|max:500',
    ]);

    // 1. Kiểm tra đặt giờ quá khứ
    if ($request->booking_date == now()->toDateString()) {
        $startTime = Carbon::parse($request->start_time);
        if ($startTime->lte(now())) {
            return back()->withErrors(['time' => 'Bạn chỉ có thể đặt sân cho các khung giờ trong tương lai.']);
        }
    }

    $court = Court::findOrFail($request->court_id);
    $user = Auth::user();

    // 2. Kiểm tra xung đột thời gian (Logic tối ưu)
    // Chúng ta kiểm tra cả status 'confirmed' VÀ 'pending' để tránh đặt chồng lên người đang chờ thanh toán
    $existingBooking = Booking::where('court_id', $request->court_id)
        ->where('booking_date', $request->booking_date)
        ->whereIn('status', ['confirmed', 'pending'])
        ->where(function($query) use ($request) {
            $query->where('start_time', '<', $request->end_time) // A < D
                  ->where('end_time', '>', $request->start_time); // B > C
        })
        ->exists();

    if ($existingBooking) {
        return back()->withErrors(['time' => 'Khung giờ này đã có người đặt hoặc đang chờ thanh toán. Vui lòng chọn giờ khác.']);
    }

    // 3. Tính tiền và lưu (Giữ nguyên logic của bạn)
    $startTime = Carbon::parse($request->start_time);
    $endTime = Carbon::parse($request->end_time);
    $hours = $endTime->diffInHours($startTime);
    $totalPrice = $hours * $court->price_per_hour;

    DB::transaction(function() use ($request, $user, $court, $totalPrice) {
        $booking = Booking::create([
            'user_id' => $user->id,
            'court_id' => $court->id,
            'booking_date' => $request->booking_date,
            'start_time' => $request->start_time,
            'end_time' => $request->end_time,
            'total_price' => $totalPrice,
            'status' => 'pending',
            'notes' => $request->notes,
        ]);

        Payment::create([
            'booking_id' => $booking->id,
            'amount' => $totalPrice,
            'payment_method' => 'pending',
            'status' => 'unpaid',
        ]);
    });

    return redirect()->route('user.bookings')->with('success', 'Đặt sân thành công! Vui lòng thanh toán.');
    }

    /**
     * Hiển thị danh sách booking của user
     */
    public function bookings()
    {
        $user = Auth::user();
        $bookings = Booking::with(['court', 'payment'])
            ->where('user_id', $user->id)
            ->orderBy('booking_date', 'desc')
            ->orderBy('start_time', 'desc')
            ->paginate(10);

        return view('user.bookings.index', compact('bookings'));
    }

    /**
     * Hủy booking
     */
    public function cancelBooking($id)
    {
        $user = Auth::user();
        $booking = Booking::where('user_id', $user->id)->findOrFail($id);

        // Kiểm tra trạng thái booking
        if ($booking->status === 'cancelled') {
            return back()->withErrors(['error' => 'Booking này đã được hủy trước đó.']);
        }

        // Kiểm tra xem booking đã bắt đầu chưa
        $bookingDateTime = Carbon::parse($booking->booking_date . ' ' . $booking->start_time);
        if ($bookingDateTime->isPast()) {
            return back()->withErrors(['error' => 'Không thể hủy booking đã bắt đầu hoặc đã kết thúc.']);
        }

        // Kiểm tra trạng thái thanh toán
        if ($booking->payment && $booking->payment->status === 'paid') {
            return back()->withErrors(['error' => 'Không thể hủy booking đã thanh toán. Vui lòng liên hệ quản trị viên để được hỗ trợ.']);
        }

        $booking->update(['status' => 'cancelled']);

        return back()->with('success', 'Hủy đặt sân thành công!');
    }

    /**
     * Hiển thị form chỉnh sửa booking
     */
    public function editBooking($id)
    {
        $user = Auth::user();
        $booking = Booking::with('court')
            ->where('user_id', $user->id)
            ->findOrFail($id);

        // Kiểm tra trạng thái booking
        if ($booking->status === 'cancelled') {
            return back()->withErrors(['error' => 'Không thể chỉnh sửa booking đã hủy.']);
        }

        // Kiểm tra xem booking đã bắt đầu chưa
        $bookingDateTime = Carbon::parse($booking->booking_date . ' ' . $booking->start_time);
        if ($bookingDateTime->isPast()) {
            return back()->withErrors(['error' => 'Không thể chỉnh sửa booking đã bắt đầu hoặc đã kết thúc.']);
        }

        // Kiểm tra trạng thái thanh toán
        if ($booking->payment && $booking->payment->status === 'paid') {
            return back()->withErrors(['error' => 'Không thể chỉnh sửa booking đã thanh toán. Vui lòng liên hệ quản trị viên để được hỗ trợ.']);
        }

        // Get booked slots for the booking date, excluding current booking
        $bookings = Booking::where('court_id', $booking->court_id)
            ->where('booking_date', $booking->booking_date)
            ->where('status', 'confirmed')
            ->where('id', '!=', $booking->id)
            ->get(['start_time', 'end_time']);

        $bookedSlots = [];
        foreach ($bookings as $b) {
            $start = Carbon::parse($b->start_time);
            $end = Carbon::parse($b->end_time);

            while ($start < $end) {
                $bookedSlots[] = $start->format('H:i');
                $start->addHour();
            }
        }

        return view('user.bookings.edit', compact('booking', 'bookedSlots'));
    }

    /**
     * Cập nhật booking
     */
   public function updateBooking(Request $request, $id)
{
    $user = Auth::user();
    $booking = Booking::with('court')->where('user_id', $user->id)->findOrFail($id);

    // Chặn sửa nếu đã hủy/thanh toán/quá hạn (Giữ nguyên các check của bạn)
    if ($booking->status === 'cancelled') return back()->withErrors(['error' => 'Không thể sửa booking đã hủy.']);

    $request->validate([
        'booking_date' => 'required|date|after_or_equal:today',
        'start_time' => 'required|date_format:H:i',
        'end_time' => 'required|date_format:H:i|after:start_time',
        'notes' => 'nullable|string|max:500',
    ]);

    // Kiểm tra xung đột thời gian (trừ chính nó ra)
    $existingBooking = Booking::where('court_id', $booking->court_id)
        ->where('booking_date', $request->booking_date)
        ->whereIn('status', ['confirmed', 'pending'])
        ->where('id', '!=', $id) // Quan trọng: Loại trừ chính nó
        ->where(function($query) use ($request) {
            $query->where('start_time', '<', $request->end_time)
                  ->where('end_time', '>', $request->start_time);
        })
        ->exists();

    if ($existingBooking) {
        return back()->withErrors(['time' => 'Thời gian này đã được người khác đặt.']);
    }

    // Tính lại giá và cập nhật
    $startTime = Carbon::parse($request->start_time);
    $endTime = Carbon::parse($request->end_time);
    $hours = $endTime->diffInHours($startTime);
    $totalPrice = $hours * $booking->court->price_per_hour;

    DB::transaction(function() use ($booking, $request, $totalPrice) {
        $booking->update([
            'booking_date' => $request->booking_date,
            'start_time' => $request->start_time,
            'end_time' => $request->end_time,
            'total_price' => $totalPrice,
            'notes' => $request->notes,
        ]);

        if ($booking->payment) {
            $booking->payment->update(['amount' => $totalPrice]);
        }
    });

    return redirect()->route('user.bookings')->with('success', 'Cập nhật thành công!');
}

    /**
     * Hiển thị map với vị trí các sân
     */
    public function map()
    {
        $courts = Court::where('status', 'available')
            ->whereNotNull('latitude')
            ->whereNotNull('longitude')
            ->get();

        // Get today's date
        $today = now()->toDateString();

        // Get all bookings for today
        $bookings = Booking::where('booking_date', $today)
            ->where('status', 'confirmed')
            ->get(['court_id', 'start_time', 'end_time']);

        // Calculate available hours for each court
        $courts->each(function($court) use ($bookings) {
            $courtBookings = $bookings->where('court_id', $court->id);
            $availableHours = $this->getAvailableHours($courtBookings);
            $court->available_hours = $availableHours;
        });

        return view('user.map', compact('courts'));
    }

    /**
     * Get available hours string for display
     */
    private function getAvailableHours($bookings)
    {
        $operatingHours = range(6, 22); // 6 AM to 10 PM
        $bookedHours = [];

        foreach ($bookings as $booking) {
            $start = Carbon::parse($booking->start_time)->hour;
            $end = Carbon::parse($booking->end_time)->hour;

            for ($hour = $start; $hour < $end; $hour++) {
                $bookedHours[] = $hour;
            }
        }

        $bookedHours = array_unique($bookedHours);
        $availableHours = array_diff($operatingHours, $bookedHours);

        if (empty($availableHours)) {
            return 'Fully booked today';
        }

        $availableHours = array_values($availableHours);
        sort($availableHours);

        // Group consecutive hours
        $ranges = [];
        $start = $availableHours[0];
        $prev = $availableHours[0];

        for ($i = 1; $i < count($availableHours); $i++) {
            if ($availableHours[$i] != $prev + 1) {
                $ranges[] = $start == $prev ? $start : $start . '-' . $prev;
                $start = $availableHours[$i];
            }
            $prev = $availableHours[$i];
        }
        $ranges[] = $start == $prev ? $start : $start . '-' . $prev;

        return implode(', ', array_map(function($range) {
            if (strpos($range, '-') !== false) {
                list($start, $end) = explode('-', $range);
                return $start . ':00-' . ($end + 1) . ':00';
            } else {
                return $range . ':00-' . ($range + 1) . ':00';
            }
        }, $ranges));
    }

    /**
     * API: Lấy dữ liệu timetable cho tất cả sân
     */
   public function getTimetable(Request $request)
{
    $date = $request->get('date', now()->toDateString());
    $now = now(); // Lấy thời điểm hiện tại
    $isToday = $date === $now->toDateString();

    $courts = Court::where('status', 'available')->orderBy('name')->get(['id', 'name']);

    // 1. Tạo danh sách khung giờ (Ví dụ từ 05:00 đến 22:00)
    $timeSlots = [];
    $start = Carbon::createFromTime(5, 0);
    $end   = Carbon::createFromTime(22, 0);

    while ($start <= $end) {
        $timeSlots[] = $start->format('H:i');
        $start->addHour();
    }

    // 2. Lấy các booking đã có
    $bookings = Booking::where('booking_date', $date)
        ->where('status', 'confirmed')
        ->get(['court_id', 'start_time', 'end_time']);

    $bookedSlots = [];
    foreach ($courts as $court) { $bookedSlots[$court->id] = []; }

    foreach ($bookings as $booking) {
        $bStart = Carbon::parse($booking->start_time);
        $bEnd   = Carbon::parse($booking->end_time);
        while ($bStart < $bEnd) {
            $bookedSlots[$booking->court_id][] = $bStart->format('H:i');
            $bStart->addHour();
        }
    }

    // 3. LOGIC QUAN TRỌNG: Xác định slot quá khứ
    $pastSlots = [];
    foreach ($timeSlots as $slot) {
        // Tạo đối tượng thời gian cho slot đó
        $slotDateTime = Carbon::createFromFormat('Y-m-d H:i', $date . ' ' . $slot);

        // Nếu thời gian của slot nhỏ hơn hoặc bằng thời gian hiện tại
        // Ví dụ: Bây giờ là 21:05, thì slot 21:00 sẽ bị coi là quá khứ (vì đã trôi qua 5p)
        if ($slotDateTime->lte($now)) {
            $pastSlots[] = $slot;
        }
    }

    return response()->json([
        'courts'     => $courts,
        'timeSlots'  => $timeSlots,
        'bookings'   => $bookedSlots,
        'pastSlots'  => $pastSlots,
        'isToday'    => $isToday
    ]);
}

}
