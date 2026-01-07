<?php

namespace App\Exports;

use App\Models\Report;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\WithStyles;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;
use Carbon\Carbon;

class ReportsExport implements FromCollection, WithHeadings, WithMapping, WithStyles
{
    protected $category;
    protected $startDate;
    protected $endDate;

    public function __construct($category = null, $startDate = null, $endDate = null)
    {
        $this->category = $category;
        $this->startDate = $startDate;
        $this->endDate = $endDate;
    }

    /**
    * @return \Illuminate\Support\Collection
    */
    public function collection()
    {
        $query = Report::with(['resident.user', 'reportCategory', 'reportStatuses']);

        // Filter berdasarkan kategori
        if ($this->category) {
            $query->whereHas('reportCategory', function($q) {
                $q->where('name', $this->category);
            });
        }

        // Filter berdasarkan tanggal
        if ($this->startDate && $this->endDate) {
            $query->whereBetween('created_at', [
                Carbon::parse($this->startDate)->startOfDay(),
                Carbon::parse($this->endDate)->endOfDay()
            ]);
        }

        return $query->latest()->get();
    }

    public function headings(): array
    {
        return [
            'No',
            'Kode Laporan',
            'Pelapor',
            'Kategori Laporan',
            'Bukti Laporan',
            'Judul Laporan',
            'Deskripsi',
            'Lokasi',
            'Status Terkini',
            'Tanggal Dibuat',
        ];
    }

    public function map($report): array
    {
        static $no = 1;

        // Ambil status terkini
        $latestStatus = $report->reportStatuses()->latest()->first();
        $currentStatus = $latestStatus ? $latestStatus->status : 'Belum Diproses';

        return [
            $no++,
            $report->code,
            $report->resident->user->name,
            $report->reportCategory->name,
            $report->image,
            $report->title,
            $report->description,
            $report->address,
            $currentStatus,
            Carbon::parse($report->created_at)->format('d/m/Y H:i'),
        ];
    }

    public function styles(Worksheet $sheet)
    {
        return [
            // Style header row
            1 => [
                'font' => ['bold' => true],
                'fill' => [
                    'fillType' => \PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID,
                    'startColor' => ['argb' => 'FFCCCCCC']
                ]
            ],
        ];
    }
}
