<?php

namespace App\Http\Controllers\User;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;
use App\Http\Requests\StoreReportRequest;
use App\Interfaces\ReportRepositoryInterface;
use App\Interfaces\ReportCategoryRepositoryInterface;
use App\Services\ComplaintClassifier; // ← IMPORT SERVICE AI

class ReportController extends Controller
{
    private ReportRepositoryInterface $reportRepository;
    private ReportCategoryRepositoryInterface $reportCategoryRepository;

    public function __construct(
        ReportRepositoryInterface $reportRepository,
        ReportCategoryRepositoryInterface $reportCategoryRepository
    ) {
        $this->reportRepository = $reportRepository;
        $this->reportCategoryRepository = $reportCategoryRepository;
    }

    /**
     * Display listing of approved reports
     */
    public function index(Request $request)
    {
        $reports = \App\Models\Report::with(['resident.user', 'reportCategory', 'reportStatuses'])
            ->where('is_approved', true)
            ->when(
                $request->category,
                fn($query) =>
                $query->whereHas('reportCategory', fn($q) => $q->where('name', $request->category))
            )
            ->when(
                $request->start_date && $request->end_date,
                fn($query) =>
                $query->whereBetween('created_at', [
                    $request->start_date . ' 00:00:00',
                    $request->end_date . ' 23:59:59'
                ])
            )
            ->latest()
            ->get();

        return view('pages.app.report.index', compact('reports'));
    }

    /**
     * Display user's own reports
     */
    public function myReport(Request $request)
    {
        $reports = $this->reportRepository->getReportsByResidentId($request->status);
        return view('pages.app.report.my-report', compact('reports'));
    }

    /**
     * Display specific report by code
     */
    public function show($code)
    {
        $report = $this->reportRepository->getReportByCode($code);
        return view('pages.app.report.show', compact('report'));
    }

    /**
     * Show take report page
     */
    public function take()
    {
        return view('pages.app.report.take');
    }

    /**
     * Show preview page
     */
    public function preview()
    {
        return view('pages.app.report.preview');
    }

    /**
     * Show form to create new report
     */
    public function create()
    {
        $categories = $this->reportCategoryRepository->getAllReportCategories();
        return view('pages.app.report.create', compact('categories'));
    }

    /**
     * ========================================
     * ✨ METHOD BARU: API untuk AI Predict Kategori (AJAX)
     * ========================================
     */
    public function predictCategory(Request $request)
    {
        try {
            $title = $request->input('title');

            // Validasi: judul minimal 10 karakter
            if (strlen($title) < 10) {
                return response()->json([
                    'success' => false,
                    'message' => 'Judul terlalu pendek, minimal 10 karakter untuk prediksi otomatis'
                ], 400);
            }

            // Panggil AI Service untuk prediksi
            $classifier = new ComplaintClassifier();
            $predictedCategory = $classifier->predict($title);

            // Mapping kategori AI ke database
            // Karena AI return lowercase, kita cari dengan LIKE
            $categoryMapping = [
                'infrastruktur' => 'infrastruktur',
                'kesehatan' => 'kesehatan',
                'kebersihan' => 'kebersihan',
                'pelayanan-publik' => 'pelayanan publik',
                'pendidikan' => 'pendidikan',
                'keamanan' => 'keamanan',
            ];

            // Get mapped name atau pakai predicted langsung
            $searchName = $categoryMapping[$predictedCategory] ?? $predictedCategory;

            // Cari kategori di database (case-insensitive)
            $category = \App\Models\ReportCategory::whereRaw('LOWER(name) LIKE ?', ['%' . strtolower($searchName) . '%'])
                ->first();

            // Fallback: cari dengan predicted category langsung
            if (!$category) {
                $category = \App\Models\ReportCategory::whereRaw('LOWER(name) LIKE ?', ['%' . strtolower($predictedCategory) . '%'])
                    ->first();
            }

            // Kalau tetap tidak ketemu
            if (!$category) {
                return response()->json([
                    'success' => false,
                    'message' => 'Kategori "' . $predictedCategory . '" tidak ditemukan di database. Silakan pilih manual.'
                ], 404);
            }

            // Return success dengan data kategori
            return response()->json([
                'success' => true,
                'category_id' => $category->id,
                'category_name' => $category->name,
                'predicted_keyword' => $predictedCategory,
                'confidence' => 'high' // bisa dikembangkan untuk confidence score
            ]);

        } catch (\Exception $e) {
            // Log error untuk debugging


            return response()->json([
                'success' => false,
                'message' => 'Terjadi kesalahan saat memprediksi kategori. Silakan pilih manual.',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * ========================================
     * ✨ METHOD UPDATED: Store dengan AI Fallback
     * ========================================
     */
    public function store(StoreReportRequest $request)
    {
        $data = $request->validated();

        // Generate kode unik
        $data['code'] = 'KLAYAN' . mt_rand(100000, 999999);
        $data['resident_id'] = Auth::user()->resident->id;

        // Upload gambar
        $data['image'] = $request->file('image')->store('assets/report/image', 'public');

        // ========================================
        // ✨ AI FALLBACK: Kalau user tidak pilih kategori
        // ========================================
        if (empty($data['report_category_id'])) {
            try {
                // Panggil AI untuk prediksi
                $classifier = new ComplaintClassifier();
                $predictedCategory = $classifier->predict($data['title']);

                // Mapping kategori
                $categoryMapping = [
                    'infrastruktur' => 'infrastruktur',
                    'kesehatan' => 'kesehatan',
                    'kebersihan' => 'kebersihan',
                    'pelayanan-publik' => 'pelayanan publik',
                    'pendidikan' => 'pendidikan',
                    'keamanan' => 'keamanan',
                ];

                $searchName = $categoryMapping[$predictedCategory] ?? $predictedCategory;

                // Cari kategori di database
                $category = \App\Models\ReportCategory::whereRaw('LOWER(name) LIKE ?', ['%' . strtolower($searchName) . '%'])
                    ->first();

                // Fallback search
                if (!$category) {
                    $category = \App\Models\ReportCategory::whereRaw('LOWER(name) LIKE ?', ['%' . strtolower($predictedCategory) . '%'])
                        ->first();
                }

                // Set kategori hasil AI
                if ($category) {
                    $data['report_category_id'] = $category->id;
                }

            } catch (\Exception $e) {
                // Kalau AI error, log tapi tetap lanjut

                // Bisa set default kategori atau biarkan null
                // Misalnya set ke kategori "Lain-lain" jika ada
            }
        }

        // Simpan laporan ke database
        $report = $this->reportRepository->createReport($data);

        // Redirect ke halaman sukses
        return redirect()->route('report.success');
    }

    /**
     * Show success page after creating report
     */
    public function success()
    {
        return view('pages.app.report.success');
    }
}
