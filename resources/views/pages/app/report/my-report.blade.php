@extends('layouts.app')

@section('title', 'Laporanmu')

@section('content')

    <ul class="nav nav-tabs" id="filter-tab" role="tablist">
        <li class="nav-item" role="presentation">
            <a class="nav-link {{ (!request('status') || request('status') === 'delivered') ? 'active' : '' }}"
                href="{{ url()->current() }}?status=delivered" role="tab">
                Terkirim
            </a>
        </li>
        <li class="nav-item" role="presentation">
            <a class="nav-link {{ request('status') === 'in_process' ? 'active' : '' }}"
                href="{{ url()->current() }}?status=in_process" role="tab">
                Diproses
            </a>
        </li>
        <li class="nav-item" role="presentation">
            <a class="nav-link {{ request('status') === 'completed' ? 'active' : '' }}"
                href="{{ url()->current() }}?status=completed" role="tab">
                Selesai
            </a>
        </li>
        <li class="nav-item" role="presentation">
            <a class="nav-link {{ request('status') === 'rejected' ? 'active' : '' }}"
                href="{{ url()->current() }}?status=rejected" role="tab">
                Ditolak
            </a>
        </li>
    </ul>

    {{-- HANYA 1 TAB PANE (DINAMIS) --}}
    <div class="tab-content" id="myTabContent">
        <div class="tab-pane fade show active" role="tabpanel" tabindex="0">
            <div class="d-flex flex-column gap-3 mt-3">
                @forelse ($reports as $report)
                    <div class="card card-report border-0 shadow-none">
                        <a href="{{ route('report.show', $report->code) }}" class="text-decoration-none text-dark">
                            <div class="card-body p-0">
                                <div class="card-report-image position-relative mb-2">
                                    <img src="{{ asset('storage/' . $report->image) }}" alt="{{ $report->title }}">

                                    @php
                                        $lastStatus = $report->reportStatuses->last();
                                        $statusValue = $lastStatus ? $lastStatus->status : null;
                                    @endphp

                                    @if ($statusValue === 'delivered')
                                        <div class="badge-status on-process">
                                            Terkirim
                                        </div>
                                    @elseif ($statusValue === 'in_process')
                                        <div class="badge-status on-process">
                                            Diproses
                                        </div>
                                    @elseif ($statusValue === 'completed')
                                        <div class="badge-status done">
                                            Selesai
                                        </div>
                                    @elseif ($statusValue === 'rejected' || !$report->is_approved)
                                        <div class="badge bg-danger fs-7 px-3 py-3 position-absolute bottom-0 start-0 m-2">
                                            Ditolak
                                        </div>
                                    @endif
                                </div>

                                <div class="d-flex justify-content-between align-items-end mb-2">
                                    <div class="d-flex align-items-center">
                                        <img src="{{ asset('assets/app/images/icons/MapPin.png') }}" alt="map pin"
                                            class="icon me-2">
                                        <p class="text-primary city mb-0">
                                            {{ \Str::limit($report->address, 20) }}
                                        </p>
                                    </div>

                                    <p class="text-secondary date mb-0">
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
                    <div class="d-flex flex-column justify-content-center align-items-center" style="height: 75vh">
                        <div id="lottie"></div>
                        <h5 class="mt-3">
                            @php
                                $currentStatus = request('status', 'delivered');
                                $statusMessages = [
                                    'delivered' => 'Belum ada laporan terkirim',
                                    'in_process' => 'Belum ada laporan diproses',
                                    'completed' => 'Belum ada laporan selesai',
                                    'rejected' => 'Belum ada laporan ditolak'
                                ];
                            @endphp
                            {{ $statusMessages[$currentStatus] ?? 'Belum ada laporan' }}
                        </h5>
                        <a href="{{ route('report.take') }}" class="btn btn-primary py-2 px-4 mt-3">
                            Buat Laporan
                        </a>
                    </div>
                @endforelse
            </div>
        </div>
    </div>

@endsection

@section('scripts')
    <script src="https://cdnjs.cloudflare.com/ajax/libs/bodymovin/5.12.2/lottie.min.js"></script>
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const lottieContainer = document.getElementById('lottie');

            if (lottieContainer) {
                bodymovin.loadAnimation({
                    container: lottieContainer,
                    renderer: 'svg',
                    loop: true,
                    autoplay: true,
                    path: '{{ asset('assets/app/lottie/not-found.json') }}'
                });
            }
        });
    </script>
@endsection
