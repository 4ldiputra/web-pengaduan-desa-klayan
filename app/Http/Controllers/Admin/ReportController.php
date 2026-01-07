<?php

namespace App\Http\Controllers\Admin;

use App\Exports\ReportsExport;
use Carbon\Carbon;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Http\Requests\StoreReportRequest;
use App\Http\Requests\UpdateReportRequest;
use App\Interfaces\ReportRepositoryInterface;
use App\Interfaces\ResidentRepositoryInterface;
use RealRashid\SweetAlert\Facades\Alert as Swal;
use App\Interfaces\ReportCategoryRepositoryInterface;
use Barryvdh\DomPDF\Facade\Pdf;
use Maatwebsite\Excel\Facades\Excel;

class ReportController extends Controller
{
    private ReportRepositoryInterface $reportRepository;

    private ReportCategoryRepositoryInterface $reportCategoryRepository;

    private ResidentRepositoryInterface $residentRepository;



    public function __construct(
        ReportRepositoryInterface $reportRepository,
        ReportCategoryRepositoryInterface $reportCategoryRepository,
        ResidentRepositoryInterface $residentRepository
    ) {
        $this->reportRepository = $reportRepository;
        $this->reportCategoryRepository = $reportCategoryRepository;
        $this->residentRepository = $residentRepository;
    }
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        // Ambil semua kategori laporan
        $categories = \App\Models\ReportCategory::all();

        // Filter laporan berdasarkan kategori dan rentang tanggal
        $reports = \App\Models\Report::with(['resident.user', 'reportCategory', 'reportStatuses'])
            ->when(
                $request->category,
                fn($query) =>
                $query->whereHas('reportCategory', fn($q) => $q->where('name', $request->category))
            )
            ->when(
                $request->start_date && $request->end_date,
                fn($query) =>
                $query->whereBetween('created_at', [
                    Carbon::parse($request->start_date)->startOfDay(),
                    Carbon::parse($request->end_date)->endOfDay()
                ])
            )
            ->latest()
            ->get();

        // Mengirimkan laporan dan kategori ke view
        return view('pages.admin.report.index', compact('reports', 'categories'));
    }

      public function approve(string $id)
    {
        $report = $this->reportRepository->getReportById($id);

        // Update approval status
        $report->update([
            'is_approved' => true
        ]);

        // Hapus status rejected/ditolak yang mungkin ada sebelumnya
        $report->reportStatuses()
            ->whereIn('status', ['rejected', 'ditolak'])
            ->delete();

        // Cek apakah sudah ada status delivered
        $hasDelivered = $report->reportStatuses()
            ->whereIn('status', ['delivered', 'terkirim'])
            ->exists();

        // Tambah status delivered jika belum ada
        if (!$hasDelivered) {
            $report->reportStatuses()->create([
                'status' => 'delivered',  // ← UBAH KE DELIVERED
                'description' => 'Laporan telah diterima dan akan segera ditindaklanjuti',
                'created_at' => now(),
                'updated_at' => now()
            ]);
        }

        Swal::toast('Laporan Berhasil Di-approve dan Terkirim', 'success')->timerProgressBar();
        return redirect()->back();
    }

    /**
     * Unapprove/Reject report
     */
    public function unapprove(string $id)
    {
        $report = $this->reportRepository->getReportById($id);

        // Update approval status
        $report->update([
            'is_approved' => false
        ]);

        // Hapus semua status kecuali rejected
        $report->reportStatuses()
            ->whereNotIn('status', ['rejected', 'ditolak'])
            ->delete();

        // Cek apakah sudah ada status rejected
        $hasRejected = $report->reportStatuses()
            ->whereIn('status', ['rejected', 'ditolak'])
            ->exists();

        // Tambah status rejected jika belum ada
        if (!$hasRejected) {
            $report->reportStatuses()->create([
                'status' => 'rejected',
                'description' => 'Laporan ditolak oleh admin',
                'created_at' => now(),
                'updated_at' => now()
            ]);
        }

        Swal::toast('Laporan Berhasil Ditolak', 'warning')->timerProgressBar();
        return redirect()->back();
    }


    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        $residents = $this->residentRepository->getAllResidents();
        $categories = $this->reportCategoryRepository->getAllReportCategories();

        return view('pages.admin.report.create', compact('residents', 'categories'));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(StoreReportRequest $request)
    {
        $data = $request->validated();

        $data['code'] = 'KLAYAN' . mt_rand(100000, 999999);

        $data['image'] = $request->file('image')->store('assets/report/image', 'public');

        $this->reportRepository->createReport($data);

        Swal::toast('Data Kategori Berhasil Ditambahkan', 'success')->timerProgressBar();

        return redirect()->route('admin.report.index');
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        $report = $this->reportRepository->getReportById($id);

        return view('pages.admin.report.show', compact('report'));
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(string $id)
    {
        $report = $this->reportRepository->getReportById($id);

        $residents = $this->residentRepository->getAllResidents();
        $categories = $this->reportCategoryRepository->getAllReportCategories();

        return view('pages.admin.report.edit', compact('report', 'residents', 'categories'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(UpdateReportRequest $request, string $id)
    {
        $data = $request->validated();

        if ($request->image) {
            $data['image'] = $request->file('image')->store('assets/report/image', 'public');
        }

        $this->reportRepository->updateReport($id, $data);

        Swal::toast('Data Laporan Berhasil Diupdate', 'success')->timerProgressBar();

        return redirect()->route('admin.report.index');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        $this->reportRepository->deteleReport($id);

        Swal::toast('Data Laporan Berhasil Dihapus', 'success')->timerProgressBar();

        return redirect()->route('admin.report.index');
    }

    public function exportExcel(Request $request)
    {
        $category = $request->get('category');
        $startDate = $request->get('start_date');
        $endDate = $request->get('end_date');

        // Generate filename dengan filter info
        $filename = 'laporan-pengaduan';

        if ($category) {
            $filename .= '-' . str_replace(' ', '-', strtolower($category));
        }

        if ($startDate && $endDate) {
            $filename .= '-' . Carbon::parse($startDate)->format('d-m-Y') . '-sampai-' . Carbon::parse($endDate)->format('d-m-Y');
        }

        $filename .= '-' . Carbon::now()->format('d-m-Y-H-i-s') . '.xlsx';

        return Excel::download(new ReportsExport($category, $startDate, $endDate), $filename);
    }

    /**
     * Export data to PDF
     */
    public function exportPdf(Request $request)
    {
        $category = $request->get('category');
        $startDate = $request->get('start_date');
        $endDate = $request->get('end_date');

        // Get filtered data
        $reports = \App\Models\Report::with(['resident.user', 'reportCategory', 'reportStatuses'])
            ->when(
                $category,
                fn($query) =>
                $query->whereHas('reportCategory', fn($q) => $q->where('name', $category))
            )
            ->when(
                $startDate && $endDate,
                fn($query) =>
                $query->whereBetween('created_at', [
                    Carbon::parse($startDate)->startOfDay(),
                    Carbon::parse($endDate)->endOfDay()
                ])
            )
            ->latest()
            ->get();

        // Generate PDF
        $pdf = Pdf::loadView('pages.admin.report.pdf', compact(
            'reports',
            'category',
            'startDate',
            'endDate'
        ));

        // Set paper size dan orientation
        $pdf->setPaper('A4', 'landscape');

        // Generate filename
        $filename = 'laporan-pengaduan';

        if ($category) {
            $filename .= '-' . str_replace(' ', '-', strtolower($category));
        }

        if ($startDate && $endDate) {
            $filename .= '-' . Carbon::parse($startDate)->format('d-m-Y') . '-sampai-' . Carbon::parse($endDate)->format('d-m-Y');
        }

        $filename .= '-' . Carbon::now()->format('d-m-Y-H-i-s') . '.pdf';

        return $pdf->download($filename);
    }
}
