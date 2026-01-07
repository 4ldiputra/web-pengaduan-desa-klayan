<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Laporan Data Pengaduan</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            font-size: 12px;
            margin: 20px;
        }

        .header {
            text-align: center;
            margin-bottom: 30px;
            border-bottom: 2px solid #333;
            padding-bottom: 20px;
        }

        .header h1 {
            margin: 0;
            color: #333;
            font-size: 18px;
        }

        .header h2 {
            margin: 5px 0;
            color: #666;
            font-size: 14px;
            font-weight: normal;
        }

        .filter-info {
            margin-bottom: 20px;
            background-color: #f8f9fa;
            padding: 10px;
            border-radius: 5px;
        }

        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 20px;
        }

        th, td {
            border: 1px solid #ddd;
            padding: 8px;
            text-align: left;
            vertical-align: top;
        }

        th {
            background-color: #f2f2f2;
            font-weight: bold;
            text-align: center;
        }

        .no-data {
            text-align: center;
            font-style: italic;
            color: #666;
            padding: 20px;
        }

        .footer {
            margin-top: 30px;
            text-align: right;
            font-size: 10px;
            color: #666;
        }

        .status-badge {
            padding: 2px 6px;
            border-radius: 3px;
            font-size: 10px;
            color: white;
        }

        .status-pending {
            background-color: #ffc107;
        }

        .status-process {
            background-color: #17a2b8;
        }

        .status-done {
            background-color: #28a745;
        }

        .status-rejected {
            background-color: #dc3545;
        }
    </style>
</head>
<body>
    <div class="header">
        <h1>LAPORAN DATA PENGADUAN</h1>
        <h2>Sistem Informasi Pengaduan Masyarakat</h2>
    </div>

    @if($category || $startDate || $endDate)
    <div class="filter-info">
        <strong>Filter Laporan:</strong><br>
        @if($category)
            Kategori: {{ $category }}<br>
        @endif
        @if($startDate && $endDate)
            Periode: {{ \Carbon\Carbon::parse($startDate)->format('d/m/Y') }} - {{ \Carbon\Carbon::parse($endDate)->format('d/m/Y') }}<br>
        @endif
        Total Data: {{ $reports->count() }} laporan
    </div>
    @endif

    @if($reports->count() > 0)
    <table>
        <thead>
            <tr>
                <th style="width: 5%;">No</th>
                <th style="width: 12%;">Kode Laporan</th>
                <th style="width: 15%;">Pelapor</th>
                <th style="width: 12%;">Kategori</th>
                <th style="width: 20%;">Judul</th>
                <th style="width: 15%;">Lokasi</th>
                <th style="width: 10%;">Status</th>
                <th style="width: 11%;">Tanggal</th>
            </tr>
        </thead>
        <tbody>
            @foreach($reports as $index => $report)
            @php
                $latestStatus = $report->reportStatuses()->latest()->first();
                $currentStatus = $latestStatus ? $latestStatus->status : 'Belum Diproses';

                $statusClass = match($currentStatus) {
                    'Selesai', 'Done' => 'status-done',
                    'Diproses', 'Process' => 'status-process',
                    'Ditolak', 'Rejected' => 'status-rejected',
                    default => 'status-pending'
                };
            @endphp
            <tr>
                <td style="text-align: center;">{{ $index + 1 }}</td>
                <td>{{ $report->code }}</td>
                <td>{{ $report->resident->user->name }}</td>
                <td>{{ $report->reportCategory->name }}</td>
                <td>{{ Str::limit($report->title, 30) }}</td>
                <td>{{ Str::limit($report->address, 25) }}</td>
                <td style="text-align: center;">
                    <span class="status-badge {{ $statusClass }}">
                        {{ $currentStatus }}
                    </span>
                </td>
                <td style="text-align: center;">
                    {{ \Carbon\Carbon::parse($report->created_at)->format('d/m/Y') }}
                </td>
            </tr>
            @endforeach
        </tbody>
    </table>
    @else
    <div class="no-data">
        Tidak ada data laporan yang sesuai dengan filter yang dipilih.
    </div>
    @endif

    <div class="footer">
        Dicetak pada: {{ \Carbon\Carbon::now()->format('d/m/Y H:i:s') }}
    </div>
</body>
</html>
