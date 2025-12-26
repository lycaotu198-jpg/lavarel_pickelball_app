<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\MonthlyRental;
use App\Models\Booking;
use App\Models\User;
use App\Models\Court;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class MonthlyRentalController extends Controller
{
    public function index()
    {
        $rentals = MonthlyRental::with('user', 'court')->get();
        return view('admin.monthly_rentals.index', compact('rentals'));
    }

    public function create()
    {
        return view('admin.monthly_rentals.create', [
            'users' => User::all(),
            'courts' => Court::all(),
        ]);
    }

    public function store(Request $request)
    {
        $request->validate([
            'user_id' => 'required|exists:users,id',
            'court_id' => 'required|exists:courts,id',
            'start_date' => 'required|date',
            'end_date' => 'required|date|after_or_equal:start_date',
            'week_days' => 'required|array',
            'start_time' => 'required',
            'end_time' => 'required|after:start_time',
            'monthly_price' => 'required|numeric|min:0',
        ]);

        DB::beginTransaction();

        try {
            /* =========================
               1️⃣ SINH DANH SÁCH NGÀY
            ========================= */
            $dates = [];
            $current = Carbon::parse($request->start_date);
            $endDate = Carbon::parse($request->end_date);

            while ($current <= $endDate) {
                if (in_array(strtolower($current->format('D')), $request->week_days)) {
                    $dates[] = $current->toDateString();
                }
                $current->addDay();
            }

            if (empty($dates)) {
                throw new \Exception('❌ Không có ngày hợp lệ trong khoảng đã chọn');
            }

            /* =========================
               2️⃣ CHECK TRÙNG LỊCH
            ========================= */
            foreach ($dates as $date) {
                $exists = Booking::where('court_id', $request->court_id)
                    ->where('booking_date', $date)
                    ->where(function ($q) use ($request) {
                        $q->where('start_time', '<', $request->end_time)
                          ->where('end_time', '>', $request->start_time);
                    })
                    ->exists();

                if ($exists) {
                    throw new \Exception("❌ Trùng lịch ngày {$date}");
                }
            }
            
            /* =========================
               3️⃣ TẠO HỢP ĐỒNG THUÊ THÁNG
            ========================= */
            $monthly = MonthlyRental::create([
                'user_id' => $request->user_id,
                'court_id' => $request->court_id,
                'start_date' => $request->start_date,
                'end_date' => $request->end_date,
                'week_days' => $request->week_days,
                'start_time' => $request->start_time,
                'end_time' => $request->end_time,
                'monthly_price' => $request->monthly_price,
                'status' => 'active',
            ]);

            /* =========================
               4️⃣ SINH BOOKING CON
            ========================= */
            foreach ($dates as $date) {
                Booking::create([
                    'user_id' => $request->user_id,
                    'court_id' => $request->court_id,
                    'monthly_rental_id' => $monthly->id,
                    'booking_date' => $date,
                    'start_time' => $request->start_time,
                    'end_time' => $request->end_time,
                    'total_price' => 0,
                    'status' => 'confirmed',
                ]);
            }

            /* =========================
               5️⃣ TẠO PAYMENT
            ========================= */
            $monthly->payment()->create([
                'amount' => $request->monthly_price,
                'method' => 'bank_transfer',
                'status' => 'paid',
            ]);

            DB::commit();

        } catch (\Exception $e) {
            DB::rollBack();
            return back()
                ->withInput()
                ->withErrors(['time' => $e->getMessage()]);
        }

        return redirect()
            ->route('admin.monthly-rentals.index')
            ->with('success', '✅ Thuê sân theo tháng thành công');
    }
}
