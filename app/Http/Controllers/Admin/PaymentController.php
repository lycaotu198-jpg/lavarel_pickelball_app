<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Booking;
use App\Models\Payment;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Log;

class PaymentController extends Controller
{
    /**
     * Danh sách hóa đơn (booking theo giờ)
     */
    public function index()
    {
        $payments = Payment::with('booking.user', 'booking.court')
            ->whereNotNull('booking_id') // ✅ chỉ payment của booking
            ->latest()
            ->get();

        return view('Admin.payments.index', compact('payments'));
    }

    /**
     * Trang thanh toán QR cho 1 booking
     */
    public function pay(Booking $booking)
    {
        $payment = $booking->payment;

        if (!$payment) {
            $payment = Payment::create([
                'booking_id' => $booking->id,
                'amount'     => $booking->total_price,
                'method'     => 'bank_transfer',
                'status'     => 'unpaid',
            ]);
        }

        return view('Admin.payments.pay', compact('booking', 'payment'));
    }

    /**
     * Xác nhận đã chuyển khoản QR
     */
    public function confirmTransfer(Booking $booking)
    {
        $payment = $booking->payment;

        if (!$payment) {
            return back()->with('error', '❌ Không tìm thấy hóa đơn');
        }

        if ($payment->status === 'paid') {
            return back()->with('error', '⚠️ Hóa đơn đã được thanh toán');
        }

        DB::transaction(function () use ($payment, $booking) {

            $payment->update([
                'status'  => 'paid',
                'method'  => 'bank_transfer',
                'paid_at' => now(),
            ]);

            $booking->update([
                'status' => 'confirmed',
            ]);
        });
        Log::info("Xác nhận thanh toán QR: Booking ID {$booking->id}, Payment ID {$payment->id}");
        return redirect()->route('admin.payments.index')
            ->with('success', '✅ Xác nhận thanh toán QR thành công');
    }

    /**
     * Trang tạo hóa đơn thủ công
     */
    public function create()
    {
        $bookings = Booking::whereDoesntHave('payment')
            ->orWhereHas('payment', function ($q) {
                $q->where('status', 'unpaid');
            })
            ->with('user', 'court')
            ->get();

        return view('Admin.payments.create', compact('bookings'));
    }

    /**
     * Thanh toán thủ công (booking theo giờ)
     */
    public function storeManual(Request $request)
    {
        $request->validate([
            'booking_id' => 'required|exists:bookings,id',
            'method'     => 'required|in:cash,bank_transfer,momo,vnpay',
        ]);

        $booking = Booking::findOrFail($request->booking_id);
        $payment = $booking->payment;

        if ($payment && $payment->status === 'paid') {
            return back()->with('error', '⚠️ Booking đã được thanh toán');
        }

        DB::transaction(function () use ($booking, $payment, $request) {

            if (!$payment) {
                $payment = Payment::create([
                    'booking_id' => $booking->id,
                    'amount'     => $booking->total_price,
                ]);
            }

            $payment->update([
                'method'  => $request->method,
                'status'  => 'paid',
                'paid_at' => now(),
            ]);

            $booking->update([
                'status' => 'confirmed',
            ]);
        });

        return redirect()->route('admin.payments.index')
            ->with('success', '✅ Thanh toán thành công');
    }
}
