<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Report;

class ReportMapController extends Controller
{
    public function index()
    {
        // Admin bisa lihat semua laporan (termasuk yang ditolak)
        // Tapi jika ingin exclude yang ditolak juga, gunakan filter yang sama

        $reports = Report::with(['reportCategory', 'reportStatuses'])
            ->whereNotNull('latitude')
            ->whereNotNull('longitude')
            // Uncomment jika admin juga tidak ingin lihat yang ditolak:
            // ->whereHas('reportStatuses', function($query) {
            //     $query->where('status', '!=', 'rejected')
            //           ->where('status', '!=', 'ditolak');
            // })
            ->get();

        // Kirim ke view
        return view('pages.admin.report.map', compact('reports'));
    }
}
