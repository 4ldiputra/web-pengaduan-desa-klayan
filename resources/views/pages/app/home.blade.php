@extends('layouts.app')

@section('title', 'Home')

@section('content')

    <h6 class="greeting">Hi, {{ Auth::user()->name }} 👋</h6>
    <h4 class="home-headline">Laporkan masalahmu dan kami segera atasi itu</h4>

    <div class="d-flex align-items-center justify-content-between gap-4 py-3 overflow-auto" id="category"
        style="white-space: nowrap;">

        @foreach ($categories as $category)
            <a href="{{ route('report.index', ['category' => $category->name]) }}" class="category d-inline-block">
                <div class="icon">
                    <img src="{{ asset('storage/' . $category->image) }}" alt="icon">
                </div>
                <p>{{ $category->name }}</p>
            </a>
        @endforeach


    </div>

    <div class="py-3" id="reports">
        <div class="d-flex justify-content-between align-items-center">
            <h6>Pengaduan terbaru</h6>
            <a href="{{ route('report.index') }}" class="text-primary text-decoration-none show-more">
                Lihat semua
            </a>
        </div>

        <div class="d-flex flex-column gap-3 mt-3">
            @forelse ($reports as $report)
                <div class="card card-report border-0 shadow-none">
                    <a href="{{ route('report.show', $report->code) }}" class="text-decoration-none text-dark">
                        <div class="card-body p-0">
                            <div class="card-report-image position-relative mb-2">
                                <img src="{{ asset('storage/' . $report->image) }}" alt="">

                                @if ($report->reportStatuses->last()->status === 'delivered')
                                    <div class="badge-status on-process">
                                        Terkirim
                                    </div>
                                @endif

                                @if ($report->reportStatuses->last()->status === 'in_process')
                                    <div class="badge-status on-process">
                                        Diproses
                                    </div>
                                @endif

                                @if ($report->reportStatuses->last()->status === 'completed')
                                    <div class="badge-status done">
                                        Selesai
                                    </div>
                                @endif
                                @if ($report->reportStatuses->last()->status === 'rejected')
                                    <div class="badge bg-danger fs-7 px-3 py-3 position-absolute bottom-0 start-0 m-2">
                                        Ditolak
                                    </div>
                                @endif

                            </div>

                            <div class="d-flex justify-content-between align-items-end mb-2">
                                <div class="d-flex align-items-center ">
                                    <img src="{{ asset('assets/app/images/icons/MapPin.png') }}" alt="map pin"
                                        class="icon me-2">
                                    <p class="text-primary city">
                                        {{ \Str::substr($report->address, 0, 20) }}...
                                    </p>
                                </div>

                                <p class="text-secondary date">
                                    {{ \Carbon\Carbon::parse($report->created_at)->format('d M Y H:i') }}
                                </p>
                            </div>

                            <h1 class="card-title">
                                {{ $report->title }}
                            </h1>
                        </div>
                    </a>
                </div>
            @empty
                <div class="d-flex flex-column justify-content-center align-items-center" style="height: 75vh"
                    id="no-reports">
                    <div id="lottie"></div>
                    <h5 class="mt-3">Belum ada laporan</h5>
                    <p class="text-secondary text-center mt-2" style="max-width: 250px;">
                        Laporan Anda akan muncul setelah disetujui oleh admin.
                    </p>
                </div>
            @endforelse

        </div>
    </div>

@endsection>

@section('scripts')

    <script src="https://cdnjs.cloudflare.com/ajax/libs/bodymovin/5.12.2/lottie.min.js"></script>
    <script>
        var animation = bodymovin.loadAnimation({
            container: document.getElementById('lottie'),
            renderer: 'svg',
            loop: true,
            autoplay: true,
            path: '{{ asset('assets/app/lottie/not-found.json') }}'
        })
    </script>

@endsection
