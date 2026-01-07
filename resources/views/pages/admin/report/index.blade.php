@extends('layouts.admin')

@section('title', 'Data Laporan')

@section('content')
    <!-- Form Filter -->
    <form action="{{ route('admin.report.index') }}" method="GET" class="mb-3" id="filterForm">
        <div class="row">
            <!-- Filter Kategori -->
            <div class="col-md-3">
                <label for="category">Kategori Laporan</label>
                <select name="category" id="category" class="form-control">
                    <option value="">Pilih Kategori</option>
                    @foreach ($categories as $category)
                        <option value="{{ $category->name }}"
                            {{ request('category') == $category->name ? 'selected' : '' }}>
                            {{ $category->name }}
                        </option>
                    @endforeach
                </select>
            </div>

            <!-- Filter Tanggal -->
            <div class="col-md-3">
                <label for="start_date">Tanggal Mulai</label>
                <input type="date" name="start_date" id="start_date" class="form-control"
                    value="{{ request('start_date') }}">
            </div>

            <div class="col-md-3">
                <label for="end_date">Tanggal Akhir</label>
                <input type="date" name="end_date" id="end_date" class="form-control" value="{{ request('end_date') }}">
            </div>

            <!-- Tombol Filter -->
            <div class="col-md-3">
                <button type="submit" class="btn btn-primary mt-4">Filter</button>
            </div>
        </div>
    </form>

    <!-- Action Buttons Row -->
    <div class="row mb-3">
        <div class="col-md-6">
            <!-- Tombol Tambah Data -->
            <a href="{{ route('admin.report.create') }}" class="btn btn-primary">
                <i class="fas fa-plus"></i> Tambah Data
            </a>
        </div>
        <div class="col-md-6 text-right">
            <!-- Export Buttons -->
            <div class="btn-group" role="group">
                <button type="button" class="btn btn-success mr-2" onclick="exportData('excel')">
                    <i class="fas fa-file-excel"></i> Export Excel
                </button>
                <button type="button" class="btn btn-danger" onclick="exportData('pdf')">
                    <i class="fas fa-file-pdf"></i> Export PDF
                </button>
            </div>
        </div>
    </div>

    <!-- Info Panel -->
    @if (request()->hasAny(['category', 'start_date', 'end_date']))
        <div class="alert alert-info">
            <strong>Filter Aktif:</strong>
            @if (request('category'))
                <span class="badge badge-primary">Kategori: {{ request('category') }}</span>
            @endif
            @if (request('start_date') && request('end_date'))
                <span class="badge badge-info">
                    Periode: {{ \Carbon\Carbon::parse(request('start_date'))->format('d/m/Y') }} -
                    {{ \Carbon\Carbon::parse(request('end_date'))->format('d/m/Y') }}
                </span>
            @endif
            <span class="badge badge-success">Total: {{ $reports->count() }} laporan</span>
        </div>
    @endif

    <div class="card shadow mb-4">
        <div class="card-header py-3">
            <h6 class="m-0 font-weight-bold text-primary">Daftar Data Laporan</h6>
        </div>
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-bordered" id="dataTable" width="100%" cellspacing="0">
                    <thead>
                        <tr>
                            <th>No</th>
                            <th>Kode Laporan</th>
                            <th>Pelapor</th>
                            <th>Kategori Laporan</th>
                            <th>Judul Laporan</th>
                            {{-- <th>Kalimat Baku</th>
                            <th>Prioritas</th> --}}
                            <th>Bukti Laporan</th>
                            <th>Status</th>
                            <th>Approval</th>
                            <th>Tanggal</th>
                            <th>Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($reports as $report)
                            @php
                                $latestStatus = $report->reportStatuses()->latest()->first();
                                $currentStatus = $latestStatus ? $latestStatus->status : 'Belum Diproses';
                                $statusLower = strtolower($currentStatus);

                                // Penyesuaian badge status sesuai dengan map
                                $statusClass = 'secondary'; // default
                                $statusIcon = 'fas fa-question-circle'; // default icon

                                if (str_contains($statusLower, 'process') || str_contains($statusLower, 'proses')) {
                                    $statusClass = 'warning';
                                    $statusIcon = 'fas fa-clock';
                                    $currentStatus = 'Proses';
                                } elseif (
                                    str_contains($statusLower, 'completed') ||
                                    str_contains($statusLower, 'selesai') ||
                                    str_contains($statusLower, 'done')
                                ) {
                                    $statusClass = 'success';
                                    $statusIcon = 'fas fa-check-circle';
                                    $currentStatus = 'Selesai';
                                } elseif (
                                    str_contains($statusLower, 'delivered') ||
                                    str_contains($statusLower, 'terkirim')
                                ) {
                                    $statusClass = 'primary';
                                    $statusIcon = 'fas fa-paper-plane';
                                    $currentStatus = 'Terkirim';
                                } elseif (
                                    str_contains($statusLower, 'rejected') ||
                                    str_contains($statusLower, 'ditolak')
                                ) {
                                    $statusClass = 'danger';
                                    $statusIcon = 'fas fa-times-circle';
                                    $currentStatus = 'Ditolak';
                                } else {
                                    $statusClass = 'secondary';
                                    $statusIcon = 'fas fa-question-circle';
                                    $currentStatus = 'Lainnya';
                                }
                            @endphp
                            <tr>
                                <td>{{ $loop->iteration }}</td>
                                <td>{{ $report->code }}</td>
                                <td>{{ $report->resident->user->name }}</td>
                                <td>{{ $report->reportCategory->name }}</td>
                                <td>{{ Str::limit($report->title, 50) }}</td>
                                {{-- <td>{{ $report->kalimat_baku ?? '-' }}</td>
                                <td>
                                    @if ($report->priority)
                                        <span class="badge @if ($report->priority == 'tinggi') badge-danger @elseif($report->priority == 'sedang') badge-warning @else badge-success @endif">
                                            {{ ucfirst($report->priority) }}
                                        </span>
                                    @else
                                        <span class="badge badge-secondary">-</span>
                                    @endif
                                </td> --}}
                                <td>
                                    <img src="{{ asset('storage/' . $report->image) }}" alt="image" width="100"
                                        class="img-thumbnail">
                                </td>
                                <td>
                                    <span class="badge badge-{{ $statusClass }}">
                                        <i class="{{ $statusIcon }}"></i> {{ $currentStatus }}
                                    </span>
                                </td>
                                <td>
                                    @if($report->is_approved)
                                        <span class="badge badge-success">
                                            <i class="fas fa-check-circle"></i> Approved
                                        </span>
                                    @else
                                        <span class="badge badge-warning">
                                            <i class="fas fa-clock"></i> Pending
                                        </span>
                                    @endif
                                </td>
                                <td>
                                    <small class="text-muted">
                                        {{ \Carbon\Carbon::parse($report->created_at)->format('d M Y H:i') }}
                                    </small>
                                </td>
                                <td>
                                    <div class="btn-group-vertical" role="group" style="min-width: 120px;">
                                        <!-- Tombol Lihat & Edit -->
                                        <div class="btn-group mb-1" role="group">
                                            <a href="{{ route('admin.report.show', $report->id) }}" class="btn btn-info btn-sm" title="Lihat Detail">
                                                <i class="fas fa-eye"></i>
                                            </a>
                                            <a href="{{ route('admin.report.edit', $report->id) }}" class="btn btn-warning btn-sm" title="Edit">
                                                <i class="fas fa-edit"></i>
                                            </a>
                                        </div>

                                        <!-- Tombol Approve/Unapprove -->
                                        @if(!$report->is_approved)
                                            <form action="{{ route('admin.report.approve', $report->id) }}" method="POST" class="mb-1">
                                                @csrf
                                                <button type="submit" class="btn btn-success btn-sm btn-block" title="Approve Laporan">
                                                    <i class="fas fa-check"></i> Approve
                                                </button>
                                            </form>
                                        @else
                                            <form action="{{ route('admin.report.unapprove', $report->id) }}" method="POST" class="mb-1">
                                                @csrf
                                                <button type="submit" class="btn btn-secondary btn-sm btn-block" title="Batalkan Approve">
                                                    <i class="fas fa-times"></i> Unapprove
                                                </button>
                                            </form>
                                        @endif

                                        <!-- Tombol Hapus -->
                                        <form id="delete-form-{{ $report->id }}" action="{{ route('admin.report.destroy', $report->id) }}" method="POST">
                                            @csrf
                                            @method('DELETE')
                                            <button type="button" class="btn btn-danger btn-sm btn-block" onclick="confirmDelete('{{ $report->id }}')" title="Hapus">
                                                <i class="fas fa-trash"></i> Hapus
                                            </button>
                                        </form>
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="12" class="text-center">
                                    <div class="py-4">
                                        <i class="fas fa-inbox fa-3x text-muted mb-3"></i>
                                        <p class="text-muted">Tidak ada data laporan</p>
                                    </div>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
@endsection

@section('scripts')
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <script>
        function confirmDelete(id) {
            Swal.fire({
                title: 'Apakah Anda yakin?',
                text: "Data yang dihapus tidak dapat dikembalikan!",
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#d33',
                cancelButtonColor: '#6c757d',
                confirmButtonText: 'Ya, hapus!',
                cancelButtonText: 'Batal'
            }).then((result) => {
                if (result.isConfirmed) {
                    document.getElementById('delete-form-' + id).submit();
                }
            });
        }

        function exportData(type) {
            // Ambil parameter filter saat ini
            const category = document.getElementById('category').value;
            const startDate = document.getElementById('start_date').value;
            const endDate = document.getElementById('end_date').value;

            // Validasi tanggal
            if ((startDate && !endDate) || (!startDate && endDate)) {
                Swal.fire({
                    title: 'Perhatian!',
                    text: 'Jika ingin filter berdasarkan tanggal, mohon isi kedua field tanggal (mulai dan akhir)',
                    icon: 'warning',
                    confirmButtonText: 'OK'
                });
                return;
            }

            // Buat URL dengan parameter
            let exportUrl;
            if (type === 'excel') {
                exportUrl = "{{ route('admin.report.export.excel') }}";
            } else {
                exportUrl = "{{ route('admin.report.export.pdf') }}";
            }

            const params = new URLSearchParams();
            if (category) params.append('category', category);
            if (startDate) params.append('start_date', startDate);
            if (endDate) params.append('end_date', endDate);

            if (params.toString()) {
                exportUrl += '?' + params.toString();
            }

            // Show loading
            Swal.fire({
                title: 'Sedang memproses...',
                text: 'Mohon tunggu, file sedang dibuat',
                allowOutsideClick: false,
                allowEscapeKey: false,
                showConfirmButton: false,
                willOpen: () => {
                    Swal.showLoading();
                }
            });

            // Download file
            window.location.href = exportUrl;

            // Close loading after delay
            setTimeout(() => {
                Swal.close();
            }, 2000);
        }

        // Auto-submit form on date change untuk real-time filtering
        document.getElementById('start_date').addEventListener('change', function() {
            if (this.value && document.getElementById('end_date').value) {
                document.getElementById('filterForm').submit();
            }
        });

        document.getElementById('end_date').addEventListener('change', function() {
            if (this.value && document.getElementById('start_date').value) {
                document.getElementById('filterForm').submit();
            }
        });

        document.getElementById('category').addEventListener('change', function() {
            document.getElementById('filterForm').submit();
        });
    </script>
@endsection
