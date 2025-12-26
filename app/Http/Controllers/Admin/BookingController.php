<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Booking;
use App\Models\User;
use App\Models\Court;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;
class BookingController extends Controller
{
    public function index()
    {
         $bookings = Booking::with(['user', 'court'])
        ->latest() // 👈 ORDER BY created_at DESC
        ->get();

        return view('admin.bookings.index', compact('bookings'));
    }

    public function create()
    {

        $users = User::all();
        $courts = Court::all();
        return view('admin.bookings.create', compact('users', 'courts'));
    }

   public function store(Request $request)
{
    $request->validate([
        'user_id' => 'required|exists:users,id',
        'court_id' => 'required|exists:courts,id',
        'booking_date' => 'required|date',
        'start_time' => 'required',
        'end_time' => 'required|after:start_time',
        'total_price' => 'required|numeric|min:0',
    ]);

    // ✅ CHECK TRÙNG GIỜ THEO SÂN (CHỈ BOOKING CHƯA THANH TOÁN)
    $exists = Booking::where('court_id', $request->court_id)
        ->where('booking_date', $request->booking_date)
        ->whereHas('payment', function ($q) {
            $q->where('status', 'unpaid');
        })
        ->where(function ($query) use ($request) {
            $query->where('start_time', '<', $request->end_time)
                  ->where('end_time', '>', $request->start_time);
        })
        ->exists();

    if ($exists) {
        return back()
            ->withInput()
            ->withErrors([
                'time' => '❌ Khung giờ này đã có người đặt sân'
            ]);
    }

    DB::transaction(function () use ($request) {

        // 1️⃣ Tạo Booking
        $booking = Booking::create([
            'user_id' => $request->user_id,
            'court_id' => $request->court_id,
            'booking_date' => $request->booking_date,
            'start_time' => $request->start_time,
            'end_time' => $request->end_time,
            'total_price' => $request->total_price,
            'status' => 'pending',
        ]);

        // 2️⃣ Tạo Payment
        $booking->payment()->create([
            'amount' => $booking->total_price,
            'method' => 'cash',
            'status' => 'unpaid',
        ]);
    });

    return redirect()->route('admin.bookings.index')
        ->with('success', '✅ Tạo booking thành công');
    }
    public function edit($id)
    {
        $booking = Booking::findOrFail($id);
        $users = User::all();
        $courts = Court::all();
        return view('admin.bookings.edit', compact('booking', 'users', 'courts'));
    }

   public function update(Request $request, $id)
{
    $booking = Booking::findOrFail($id);

    // ❌ KHÔNG cho sửa nếu đã thanh toán
    if ($booking->payment && $booking->payment->status === 'paid') {
        abort(403, 'Booking đã thanh toán, không thể chỉnh sửa');
    }

    $request->validate([
        'user_id' => 'required|exists:users,id',
        'court_id' => 'required|exists:courts,id',
        'booking_date' => 'required|date',
        'start_time' => 'required',
        'end_time' => 'required|after:start_time',
        'total_price' => 'required|numeric|min:0',
    ]);

    // ✅ CHECK TRÙNG GIỜ THEO SÂN (BỎ QUA BOOKING HIỆN TẠI)
    $exists = Booking::where('court_id', $request->court_id)
        ->where('booking_date', $request->booking_date)
        ->where('id', '!=', $booking->id) // 👈 QUAN TRỌNG
        ->whereHas('payment', function ($q) {
            $q->where('status', 'unpaid');
        })
        ->where(function ($query) use ($request) {
            $query->where('start_time', '<', $request->end_time)
                  ->where('end_time', '>', $request->start_time);
        })
        ->exists();

    if ($exists) {
        return back()
            ->withInput()
            ->withErrors([
                'time' => '❌ Khung giờ này đã có người đặt sân'
            ]);
    }

    // ✅ Cập nhật booking
    $booking->update([
        'user_id' => $request->user_id,
        'court_id' => $request->court_id,
        'booking_date' => $request->booking_date,
        'start_time' => $request->start_time,
        'end_time' => $request->end_time,
        'total_price' => $request->total_price,
    ]);

    return redirect()->route('admin.bookings.index')
        ->with('success', '✅ Cập nhật đặt sân thành công');
    }

    public function delete($id)
    {
        $booking = Booking::findOrFail($id);
        return view('admin.bookings.delete', compact('booking'));
    }


    public function destroy($id)
    {
        Booking::destroy($id);
        return redirect()->route('admin.bookings.index')
            ->with('success', 'Đã xoá đặt sân');
    }



public function busyTimes(Request $request)
{
    $courtId = $request->court_id;
    $date = $request->date;
    $bookingId = $request->booking_id; // Dùng để loại trừ khi chỉnh sửa

    $bookings = Booking::where('court_id', $courtId)
        ->where('booking_date', $date)
        // Chỉ tính các sân đã xác nhận hoặc đang chờ thanh toán
        ->whereIn('status', ['confirmed', 'pending'])
        ->when($bookingId, function ($q) use ($bookingId) {
            $q->where('id', '!=', $bookingId);
        })
        ->get(['start_time', 'end_time']);

    $busySlots = [];

    foreach ($bookings as $booking) {
        $start = \Carbon\Carbon::parse($booking->start_time);
        $end   = \Carbon\Carbon::parse($booking->end_time);

        // Chạy vòng lặp từ giờ bắt đầu đến trước giờ kết thúc
        while ($start < $end) {
            $busySlots[] = $start->format('H:i');

            // QUAN TRỌNG: Nếu giao diện Admin hiển thị các nút cách nhau 1 tiếng
            // thì ở đây phải là addHour(). Nếu giao diện 30p thì dùng addMinutes(30).
            $start->addHour();
        }
    }

    // array_unique để đảm bảo không có giá trị trùng lặp,
    // array_values để reset lại index của mảng sau khi unique
    return response()->json(array_values(array_unique($busySlots)));
}

}
