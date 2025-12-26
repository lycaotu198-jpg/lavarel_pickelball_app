<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Payment;
use Carbon\Carbon;
use DB;

class DashboardController extends Controller
{
    public function index()
    {
        $todayRevenue = Payment::whereDate('paid_at', today())
            ->where('status','paid')
            ->sum('amount');

        $monthRevenue = Payment::whereMonth('paid_at', now()->month)
            ->whereYear('paid_at', now()->year)
            ->where('status','paid')
            ->sum('amount');

        $totalRevenue = Payment::where('status','paid')->sum('amount');

        $dailyRevenue = Payment::select(
                DB::raw('DATE(paid_at) as date'),
                DB::raw('SUM(amount) as total')
            )
            ->where('status','paid')
            ->groupBy('date')
            ->orderBy('date')
            ->get();

        return view('admin.dashboard', compact(
            'todayRevenue',
            'monthRevenue',
            'totalRevenue',
            'dailyRevenue'
        ));
    }
}
