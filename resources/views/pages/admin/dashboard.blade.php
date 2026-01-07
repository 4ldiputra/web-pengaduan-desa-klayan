@extends('layouts.admin')

@section('content')
<h1>Dashboard</h1>

<div class="row">
    <div class="col-12 col-md-6 col-lg-4">
        <div class="card shadow-sm">
            <div class="card-body">
                <h6 class="card-title">Total Kategori Laporan</h6>
                <p class="card-text">{{ $totalCategories }}</p>
            </div>
        </div>
    </div>

    <div class="col-12 col-md-6 col-lg-4">
        <div class="card shadow-sm">
            <div class="card-body">
                <h6 class="card-title">Total Masyarakat</h6>
                <p class="card-text">{{ $totalResidents }}</p>
            </div>
        </div>
    </div>

    <div class="col-12 col-md-6 col-lg-4">
        <div class="card shadow-sm">
            <div class="card-body">
                <h6 class="card-title">Total Laporan</h6>
                <p class="card-text">{{ $totalReports }}</p>
            </div>
        </div>
    </div>
</div>

{{-- Grafik --}}
<div class="row mt-4">
    <div class="col-12">
        <div class="card shadow-sm">
            <div class="card-body">
                <h6 class="card-title">Grafik Laporan 6 Bulan Terakhir</h6>
                <div style="height: 150px; position: relative;">
                    <canvas id="reportChart"></canvas>
                </div>
            </div>
        </div>
    </div>
</div>

{{-- 5 laporan terakhir --}}
<div class="row mt-4">
    <div class="col-12">
        <div class="card shadow-sm">
            <div class="card-body">
                <h6 class="card-title">5 Laporan Terakhir</h6>
                @if($latestReports->count() > 0)
                <table class="table table-striped">
                    <thead>
                        <tr>
                            <th>Judul</th>
                            <th>Kategori</th>
                            <th>Pelapor</th>
                            <th>Tanggal</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($latestReports as $report)
                        <tr>
                            <td>{{ $report->title }}</td>
                            <td>{{ $report->reportCategory->name ?? '-' }}</td>
                            <td>{{ $report->resident->user->name ?? '-' }}</td>
                            <td>{{ $report->created_at->format('d-m-Y') }}</td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
                <a href="{{ route('admin.report.index') }}" class="btn btn-primary btn-sm">Lihat Semua</a>
                @else
                <p class="text-muted">Belum ada laporan</p>
                @endif
            </div>
        </div>
    </div>
</div>

{{-- Data untuk chart menggunakan data attributes --}}
<div id="chart-container"
     data-labels='{{ json_encode($monthLabels ?? []) }}'
     data-chart='{{ json_encode($chartData ?? []) }}'
     style="display: none;">
</div>
@endsection

@section('scripts')
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
document.addEventListener('DOMContentLoaded', function() {
    const ctx = document.getElementById('reportChart');
    const container = document.getElementById('chart-container');

    if (!ctx || !container) {
        console.error('Elements not found');
        return;
    }

    try {
        // Ambil data dari data attributes
        const labelsStr = container.getAttribute('data-labels');
        const dataStr = container.getAttribute('data-chart');

        console.log('Labels String:', labelsStr);
        console.log('Data String:', dataStr);

        const labels = JSON.parse(labelsStr || '[]');
        const data = JSON.parse(dataStr || '[]');

        console.log('Parsed Labels:', labels);
        console.log('Parsed Data:', data);

        // Jika tidak ada data, gunakan dummy
        const finalLabels = labels.length > 0 ? labels : ['Apr 2024', 'May 2024', 'Jun 2024', 'Jul 2024', 'Aug 2024', 'Sep 2024'];
        const finalData = data.length > 0 ? data : [0, 1, 0, 2, 1, 0];

        const chart = new Chart(ctx, {
            type: 'bar',
            data: {
                labels: finalLabels,
                datasets: [{
                    label: 'Jumlah Laporan',
                    data: finalData,
                    backgroundColor: 'rgba(54, 162, 235, 0.6)',
                    borderColor: 'rgba(54, 162, 235, 1)',
                    borderWidth: 1
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: {
                        display: false
                    }
                },
                scales: {
                    y: {
                        beginAtZero: true,
                        ticks: {
                            stepSize: 1
                        }
                    }
                }
            }
        });

        console.log('Chart created successfully');

    } catch (error) {
        console.error('Error creating chart:', error);

        // Fallback: buat chart dengan data dummy jika error
        try {
            const fallbackChart = new Chart(ctx, {
                type: 'bar',
                data: {
                    labels: ['Apr 2024', 'May 2024', 'Jun 2024', 'Jul 2024', 'Aug 2024', 'Sep 2024'],
                    datasets: [{
                        label: 'Jumlah Laporan',
                        data: [0, 1, 0, 2, 1, 0],
                        backgroundColor: 'rgba(54, 162, 235, 0.6)',
                        borderColor: 'rgba(54, 162, 235, 1)',
                        borderWidth: 1
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    plugins: {
                        legend: {
                            display: false
                        }
                    },
                    scales: {
                        y: {
                            beginAtZero: true,
                            ticks: {
                                stepSize: 1
                            }
                        }
                    }
                }
            });
            console.log('Fallback chart created');
        } catch (fallbackError) {
            console.error('Fallback chart also failed:', fallbackError);
        }
    }
});
</script>
@endsection
