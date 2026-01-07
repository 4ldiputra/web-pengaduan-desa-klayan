<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Report;
use App\Models\ReportCategory;
use App\Models\Resident;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

class DashboardController extends Controller
{
    public function index()
    {
        // Total data
        $totalCategories = ReportCategory::count();
        $totalResidents = Resident::count();
        $totalReports = Report::count();

        // Buat array untuk 6 bulan terakhir dengan label yang benar
        $monthLabels = [];
        $monthKeys = [];

        for ($i = 5; $i >= 0; $i--) {
            $date = Carbon::now()->subMonths($i);
            $monthKey = $date->format('Y-m');
            $monthKeys[] = $monthKey;
            $monthLabels[] = $date->format('M Y'); // Jan 2024, Feb 2024, etc
        }

        // Ambil data laporan dari database
        $reportCounts = Report::select(
                DB::raw('DATE_FORMAT(created_at, "%Y-%m") as month'),
                DB::raw('COUNT(*) as total')
            )
            ->where('created_at', '>=', Carbon::now()->subMonths(6))
            ->groupBy('month')
            ->pluck('total', 'month')
            ->toArray();

        // Isi data chart dengan 0 untuk bulan yang tidak ada laporan
        $chartData = [];
        foreach ($monthKeys as $monthKey) {
            $chartData[] = isset($reportCounts[$monthKey]) ? (int) $reportCounts[$monthKey] : 0;
        }

        // Ambil 5 laporan terbaru dengan relasi
        $latestReports = Report::with(['reportCategory', 'resident.user'])
            ->latest()
            ->take(5)
            ->get();

        return view('pages.admin.dashboard', compact(
            'totalCategories',
            'totalResidents',
            'totalReports',
            'monthLabels',
            'chartData',
            'latestReports'
        ));
    }
}
