<?php

namespace App\Http\Controllers\User;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Interfaces\ReportRepositoryInterface;
use App\Interfaces\ReportCategoryRepositoryInterface;

class HomeController extends Controller
{
    private ReportCategoryRepositoryInterface $reportCategoryRepository;
    private ReportRepositoryInterface $reportRepository;

    public function __construct(
        ReportCategoryRepositoryInterface $reportCategoryRepository,
        ReportRepositoryInterface $reportRepository
    ) {
        $this->reportRepository = $reportRepository;
        $this->reportCategoryRepository = $reportCategoryRepository;
    }

    // Di controller yang menampilkan halaman home
    public function index()
    {
        $categories = \App\Models\ReportCategory::all();

        // Hanya tampilkan laporan yang sudah di-approve
        $reports = \App\Models\Report::with(['resident.user', 'reportCategory', 'reportStatuses'])
            ->where('is_approved', true) // Filter hanya yang sudah di-approve
            ->latest()
            ->limit(5) // Batasi jumlah laporan terbaru yang ditampilkan
            ->get();

        return view('pages.app.home', compact('reports', 'categories'));
    }
}
