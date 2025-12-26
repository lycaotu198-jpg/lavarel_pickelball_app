<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Payment;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class PaymentController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth:sanctum');
    }

    /**
     * Display a listing of payments for the authenticated user.
     */
    public function index()
    {
        $payments = Payment::whereHas('booking', function ($query) {
            $query->where('user_id', Auth::id());
        })->with('booking.court')->get();

        return response()->json($payments);
    }

    /**
     * Display the specified payment.
     */
    public function show(Payment $payment)
    {
        if ($payment->booking->user_id !== Auth::id()) {
            return response()->json(['error' => 'Unauthorized'], 403);
        }

        $payment->load('booking.court');
        return response()->json($payment);
    }

    /**
     * Confirm payment (for cash payments).
     */
    public function confirm(Request $request, Payment $payment)
    {
        if ($payment->booking->user_id !== Auth::id()) {
            return response()->json(['error' => 'Unauthorized'], 403);
        }

        if ($payment->status !== 'unpaid') {
            return response()->json(['error' => 'Payment already processed'], 400);
        }

        $payment->update([
            'status' => 'paid',
            'paid_at' => now(),
        ]);

        return response()->json($payment);
    }
}