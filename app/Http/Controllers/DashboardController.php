<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Models\Division;
use App\Models\GuestLog;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

class DashboardController extends Controller
{
    /**
     * Display the admin dashboard with overview statistics and charts.
     */
    public function index(): View
    {
        $today = Carbon::today('Asia/Jakarta')->toDateString();
        $startOfMonth = Carbon::now('Asia/Jakarta')->startOfMonth()->toDateString();
        $endOfMonth = Carbon::now('Asia/Jakarta')->endOfMonth()->toDateString();

        // 1. Statistics
        $totalToday = GuestLog::where('visit_date', $today)->count();
        $totalMonth = GuestLog::whereBetween('visit_date', [$startOfMonth, $endOfMonth])->count();
        $totalAll = GuestLog::count();
        $totalDivisions = Division::active()->count();

        // 2. Trend 7 Days (including days with 0 visits)
        $sevenDays = [];
        for ($i = 6; $i >= 0; $i--) {
            $date = Carbon::today('Asia/Jakarta')->subDays($i)->toDateString();
            $label = Carbon::parse($date)->locale('id')->isoFormat('D MMM');
            $count = GuestLog::where('visit_date', $date)->count();
            $sevenDays[] = [
                'date' => $date,
                'label' => $label,
                'count' => $count,
            ];
        }

        // 3. Top Divisions visited
        $divisionStats = Division::withCount('guestLogs')
            ->orderByDesc('guest_logs_count')
            ->take(5)
            ->get();

        // 4. Recent Guest Logs
        $recentGuests = GuestLog::with('division')
            ->latest('id')
            ->take(6)
            ->get();

        return view('admin.dashboard', compact(
            'totalToday',
            'totalMonth',
            'totalAll',
            'totalDivisions',
            'sevenDays',
            'divisionStats',
            'recentGuests'
        ));
    }
}
