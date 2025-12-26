<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Booking;
use App\Models\Court;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use Carbon\Carbon;

class BookingController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth:sanctum');
    }

    /**
     * Display a listing of bookings for the authenticated user.
     */
    public function index()
    {
        $bookings = Auth::user()->bookings()->with('court', 'payment')->get();
        return response()->json($bookings);
    }

    /**
     * Store a newly created booking.
     */
    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'court_id' => 'required|exists:courts,id',
            'booking_date' => 'required|date|after_or_equal:today',
            'start_time' => 'required|date_format:H:i',
            'end_time' => 'required|date_format:H:i|after:start_time',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        $court = Court::find($request->court_id);
        if ($court->status !== 'available') {
            return response()->json(['error' => 'Court is not available'], 400);
        }

        // Check for overlapping bookings
        $overlapping = Booking::where('court_id', $request->court_id)
            ->where('booking_date', $request->booking_date)
            ->where(function ($query) use ($request) {
                $query->whereBetween('start_time', [$request->start_time, $request->end_time])
                      ->orWhereBetween('end_time', [$request->start_time, $request->end_time])
                      ->orWhere(function ($q) use ($request) {
                          $q->where('start_time', '<=', $request->start_time)
                            ->where('end_time', '>=', $request->end_time);
                      });
            })
            ->where('status', '!=', 'cancelled')
            ->exists();

        if ($overlapping) {
            return response()->json(['error' => 'Time slot is already booked'], 409);
        }

        $start = Carbon::createFromFormat('H:i', $request->start_time);
        $end = Carbon::createFromFormat('H:i', $request->end_time);
        $hours = $end->diffInHours($start);
        $totalPrice = $hours * $court->price_per_hour;

        DB::transaction(function () use ($request, $totalPrice) {
            $booking = Booking::create([
                'user_id' => Auth::id(),
                'court_id' => $request->court_id,
                'booking_date' => $request->booking_date,
                'start_time' => $request->start_time,
                'end_time' => $request->end_time,
                'total_price' => $totalPrice,
                'status' => 'pending',
            ]);
        });

        $booking = Auth::user()->bookings()->with('court')->latest()->first();
        return response()->json($booking, 201);
    }

    /**
     * Display the specified booking.
     */
    public function show(Booking $booking)
    {
        if ($booking->user_id !== Auth::id()) {
            return response()->json(['error' => 'Unauthorized'], 403);
        }

        $booking->load('court', 'payment');
        return response()->json($booking);
    }

    /**
     * Update the specified booking.
     */
    public function update(Request $request, Booking $booking)
    {
        if ($booking->user_id !== Auth::id()) {
            return response()->json(['error' => 'Unauthorized'], 403);
        }

        if ($booking->status !== 'pending') {
            return response()->json(['error' => 'Cannot update confirmed booking'], 400);
        }

        $validator = Validator::make($request->all(), [
            'status' => 'sometimes|in:cancelled',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        $booking->update($request->only('status'));
        return response()->json($booking);
    }

    /**
     * Remove the specified booking.
     */
    public function destroy(Booking $booking)
    {
        if ($booking->user_id !== Auth::id()) {
            return response()->json(['error' => 'Unauthorized'], 403);
        }

        if ($booking->status !== 'pending') {
            return response()->json(['error' => 'Cannot delete confirmed booking'], 400);
        }

        $booking->delete();
        return response()->json(['message' => 'Booking deleted successfully']);
    }
}
