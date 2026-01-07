@extends('layouts.app')

@section('title', 'Daftar Pengaduan')

@section('content')

    <div class="py-3" id="reports">
        <div class="d-flex justify-content-between align-items-center">
            <p class="text-muted">{{ $reports->count() }} List Pengaduan</p>

            <!-- Tombol Filter -->
            <button class="btn btn-filter" type="button" data-bs-toggle="modal" data-bs-target="#filterModal">
                <i class="fa-solid fa-filter me-2"></i>
                Filter
            </button>
        </div>

        <!-- Tampilkan kategori atau tanggal jika difilter -->
        @if (request()->category)
            <p>Kategori: <strong>{{ request()->category }}</strong></p>
        @endif
        @if (request()->start_date && request()->end_date)
            <p>Filter Tanggal: <strong>{{ request()->start_date }} s/d {{ request()->end_date }}</strong></p>
        @endif

        <div class="d-flex flex-column gap-3 mt-3">
            @foreach ($reports as $report)
                <div class="card card-report border-0 shadow-none">
                    <a href="{{ route('report.show', $report->code) }}" class="text-decoration-none text-dark">
                        <div class="card-body p-0">
                            <div class="card-report-image position-relative mb-2">
                                <img src="{{ asset('storage/' . $report->image) }}" alt="">

                                @if ($report->reportStatuses->last()->status === 'delivered')
                                    <div class="badge-status on-process">Terkirim</div>
                                @endif

                                @if ($report->reportStatuses->last()->status === 'in_process')
                                    <div class="badge-status on-process">Diproses</div>
                                @endif

                                @if ($report->reportStatuses->last()->status === 'completed')
                                    <div class="badge-status done">Selesai</div>
                                @endif
                                @if ($report->reportStatuses->last()->status === 'rejected')
                                    <div class="badge-status rejected">Ditolak</div>
                                @endif

                            </div>

                            <div class="d-flex justify-content-between align-items-end mb-2">
                                <div class="d-flex align-items-center">
                                    <img src="{{ asset('assets/app/images/icons/MapPin.png') }}" alt="map pin"
                                        class="icon me-2">
                                    <p class="text-primary city">{{ $report->address }}</p>
                                </div>
                                <p class="text-secondary date">
                                    {{ \Carbon\Carbon::parse($report->created_at)->format('d M Y H:i') }}
                                </p>
                            </div>

                            <h1 class="card-title">{{ $report->title }}</h1>
                        </div>
                    </a>
                </div>
            @endforeach
        </div>
    </div>

    <!-- Modal Filter -->
    <div class="modal fade" id="filterModal" tabindex="-1" aria-labelledby="filterModalLabel" aria-hidden="true">
        <div class="modal-dialog">
            <form method="GET" action="{{ route('report.index') }}">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title" id="filterModalLabel">Filter Tanggal</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                    </div>
                    <div class="modal-body">
                        <div class="mb-3">
                            <label for="start_date" class="form-label">Tanggal Mulai</label>
                            <input type="text" class="form-control datepicker" id="start_date" name="start_date"
                                required>
                        </div>
                        <div class="mb-3">
                            <label for="end_date" class="form-label">Tanggal Selesai</label>
                            <input type="text" class="form-control datepicker" id="end_date" name="end_date" required>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="submit" class="btn btn-primary">Terapkan Filter</button>
                    </div>
                </div>
            </form>
        </div>
    </div>

@endsection

@section('scripts')
    <!-- Tambahkan ini jika belum -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/flatpickr/dist/flatpickr.min.css">
    <script src="https://cdn.jsdelivr.net/npm/flatpickr"></script>
    <script>
        flatpickr(".datepicker", {
            dateFormat: "Y-m-d",
            allowInput: true
        });
    </script>
@endsection
