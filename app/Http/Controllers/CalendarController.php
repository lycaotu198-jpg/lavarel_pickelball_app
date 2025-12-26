<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Court;
use App\Models\Booking;
use Carbon\Carbon;

class CalendarController extends Controller
{
    /**
     * Hiển thị calendar cho một sân cụ thể
     */
    public function show($courtId)
    {
        $court = Court::findOrFail($courtId);

        // Lấy ngày hiện tại và các ngày trong tuần
        $currentDate = now();
        $startOfWeek = $currentDate->copy()->startOfWeek();
        $endOfWeek = $currentDate->copy()->endOfWeek();

        return view('user.calendar.show', compact('court', 'currentDate', 'startOfWeek', 'endOfWeek'));
    }

    /**
     * API: Lấy dữ liệu booking cho calendar
     */
    public function getBookings(Request $request, $courtId)
    {
        $date = $request->get('date', now()->toDateString());

        $bookings = Booking::with(['user'])
            ->where('court_id', $courtId)
            ->where('booking_date', $date)
            ->where('status', 'confirmed')
            ->get();

        $events = [];
        foreach ($bookings as $booking) {
            $startTime = Carbon::parse($booking->start_time);
            $endTime = Carbon::parse($booking->end_time);

            $events[] = [
                'id' => $booking->id,
                'title' => 'Đã đặt',
                'start' => $date . 'T' . $startTime->format('H:i:s'),
                'end' => $date . 'T' . $endTime->format('H:i:s'),
                'backgroundColor' => '#dc3545', // Màu đỏ cho giờ đã đặt
                'borderColor' => '#dc3545',
                'textColor' => '#ffffff',
                'extendedProps' => [
                    'user_name' => $booking->user->name,
                    'court_name' => $booking->court->name,
                ]
            ];
        }

        return response()->json($events);
    }

    /**
     * API: Lấy các slot trống trong ngày
     */
    public function getAvailableSlots(Request $request, $courtId)
    {
        $date = $request->get('date', now()->toDateString());
        $duration = $request->get('duration', 1); // Số giờ muốn đặt

        // Giờ hoạt động: 6:00 - 22:00
        $operatingHours = [
            'start' => '06:00',
            'end' => '22:00'
        ];

        $startTime = Carbon::parse($operatingHours['start']);
        $endTime = Carbon::parse($operatingHours['end']);

        // Lấy các booking đã confirmed trong ngày
        $bookings = Booking::where('court_id', $courtId)
            ->where('booking_date', $date)
            ->where('status', 'confirmed')
            ->orderBy('start_time')
            ->get();

        $availableSlots = [];
        $currentTime = $startTime->copy();

        while ($currentTime->lt($endTime)) {
            $slotEndTime = $currentTime->copy()->addHours($duration);

            // Kiểm tra xem slot này có bị trùng với booking nào không
            $isAvailable = true;
            foreach ($bookings as $booking) {
                $bookingStart = Carbon::parse($booking->start_time);
                $bookingEnd = Carbon::parse($booking->end_time);

                // Kiểm tra overlap
                if ($currentTime->lt($bookingEnd) && $slotEndTime->gt($bookingStart)) {
                    $isAvailable = false;
                    break;
                }
            }

            if ($isAvailable && $slotEndTime->lte($endTime)) {
                $availableSlots[] = [
                    'start' => $currentTime->format('H:i'),
                    'end' => $slotEndTime->format('H:i'),
                    'duration' => $duration
                ];
            }

            $currentTime->addHour();
        }

        return response()->json([
            'available_slots' => $availableSlots,
            'date' => $date
        ]);
    }

    /**
     * Hiển thị calendar chung cho tất cả sân
     */
    public function overview()
    {
        $courts = Court::where('status', 'available')->get();

        return view('user.calendar.overview', compact('courts'));
    }

    /**
     * API: Lấy dữ liệu cho calendar overview
     */
    public function getOverviewData(Request $request)
    {
        $start = $request->get('start', now()->toDateString());
        $end = $request->get('end', now()->addDays(7)->toDateString());

        $bookings = Booking::with(['court', 'user'])
            ->whereBetween('booking_date', [$start, $end])
            ->where('status', 'confirmed')
            ->get();

        $events = [];
        foreach ($bookings as $booking) {
            $events[] = [
                'id' => $booking->id,
                'title' => $booking->court->name . ' - ' . $booking->user->name,
                'start' => $booking->booking_date . 'T' . $booking->start_time,
                'end' => $booking->booking_date . 'T' . $booking->end_time,
                'backgroundColor' => '#dc3545',
                'borderColor' => '#dc3545',
                'textColor' => '#ffffff',
                'extendedProps' => [
                    'court_id' => $booking->court_id,
                    'court_name' => $booking->court->name,
                    'user_name' => $booking->user->name,
                ]
            ];
        }

        return response()->json($events);
    }
}
