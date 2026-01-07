<?php

namespace App\Http\Controllers\User;

use App\Http\Controllers\Controller;
use App\Models\Report;

class ReportMapController extends Controller
{
    public function index()
    {
        // Hanya ambil laporan yang SUDAH DI-APPROVE
        $reports = Report::with(['reportCategory', 'reportStatuses'])
            ->where('is_approved', true) // ← KEY: Filter approved only
            ->whereNotNull('latitude')
            ->whereNotNull('longitude')
            ->get();

        // Kirim data ke view user
        return view('pages.app.map', compact('reports'));
    }
}
