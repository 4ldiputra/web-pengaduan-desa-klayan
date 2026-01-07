@extends('layouts.admin')

@section('content')
    <style>
        .leaflet-container {
            font-family: 'Roboto', Arial, sans-serif;
        }

        .custom-popup {
            font-family: 'Roboto', Arial, sans-serif;
        }

        .info.legend {
            background: white;
            padding: 12px;
            border-radius: 8px;
            box-shadow: 0 2px 4px rgba(0, 0, 0, 0.2);
        }
    </style>

    <div class="container-fluid">
        <h1 class="h3 mb-4 text-gray-800">Peta Laporan - Desa Klayan</h1>

        <!-- Stats Cards -->
        <div class="row mb-3">
            <div class="col-md-3">
                <div class="card border-left-success shadow-sm h-100 py-2">
                    <div class="card-body">
                        <div class="text-xs font-weight-bold text-success text-uppercase mb-1">
                            Laporan Approved
                        </div>
                        <div class="h5 mb-0 font-weight-bold text-gray-800">
                            {{ $reports->where('is_approved', true)->count() }}
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="card border-left-warning shadow-sm h-100 py-2">
                    <div class="card-body">
                        <div class="text-xs font-weight-bold text-warning text-uppercase mb-1">
                            Menunggu Approval
                        </div>
                        <div class="h5 mb-0 font-weight-bold text-gray-800">
                            {{ $reports->where('is_approved', false)->count() }}
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="card border-left-info shadow-sm h-100 py-2">
                    <div class="card-body">
                        <div class="text-xs font-weight-bold text-info text-uppercase mb-1">
                            Total Laporan
                        </div>
                        <div class="h5 mb-0 font-weight-bold text-gray-800">
                            {{ $reports->count() }}
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="card border-left-primary shadow-sm h-100 py-2">
                    <div class="card-body">
                        <div class="text-xs font-weight-bold text-primary text-uppercase mb-1">
                            Laporan Hari Ini
                        </div>
                        <div class="h5 mb-0 font-weight-bold text-gray-800">
                            {{ $reports->where('created_at', '>=', \Carbon\Carbon::today())->count() }}
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Map Card -->
        <div class="card shadow mb-4">
            <div class="card-body p-0">
                <div id="map" style="height: 600px; width: 100%;"></div>
            </div>
        </div>
    </div>

    <!-- Leaflet CSS & JS -->
    <link rel="stylesheet" href="https://unpkg.com/leaflet@1.7.1/dist/leaflet.css" />
    <script src="https://unpkg.com/leaflet@1.7.1/dist/leaflet.js"></script>

    <script>
        document.addEventListener("DOMContentLoaded", function() {
            // Koordinat Desa Klayan dari Google Maps
            var centerLat = (-6.70478 + -6.66478) / 2;
            var centerLng = (108.52841 + 108.56841) / 2;



            var map = L.map('map').setView([centerLat, centerLng], 15);

            // Gunakan tile yang mirip Google Maps
            L.tileLayer('https://mt1.google.com/vt/lyrs=r&x={x}&y={y}&z={z}', {
                attribution: '© Google Maps',
                maxZoom: 20,
            }).addTo(map);

            // Alternatif tiles (uncomment salah satu):
            // Satellite: lyrs=s
            // Hybrid: lyrs=y
            // Terrain: lyrs=p
            // Roads: lyrs=m

            // Data reports
            var reports = @json($reports);

            // Function untuk warna marker
            function getMarkerColor(report) {
                if (!report.is_approved) return '#FFA500'; // Orange

                let status = report.report_statuses?.length ?
                    report.report_statuses[report.report_statuses.length - 1].status.toLowerCase() :
                    '';

                if (status.includes('delivered') || status.includes('terkirim')) return '#2196F3';
                if (status.includes('in_process') || status.includes('proses')) return '#FFC107';
                if (status.includes('completed') || status.includes('selesai')) return '#4CAF50';
                if (status.includes('rejected') || status.includes('ditolak')) return '#F44336';
                return '#9E9E9E';
            }

            // Tambah markers untuk laporan
            reports.forEach(function(report) {
                if (report.latitude && report.longitude) {
                    var color = getMarkerColor(report);

                    // Custom icon mirip Google Maps
                    var customIcon = L.divIcon({
                        className: 'custom-marker',
                        html: `<div style="
                            background: ${color};
                            width: 24px;
                            height: 24px;
                            border-radius: 50%;
                            border: 3px solid white;
                            box-shadow: 0 2px 6px rgba(0,0,0,0.3);
                        "></div>`,
                        iconSize: [24, 24],
                        iconAnchor: [12, 12]
                    });

                    // Status text
                    let statusText = 'Pending';
                    if (report.is_approved) {
                        let status = report.report_statuses?.length ?
                            report.report_statuses[report.report_statuses.length - 1].status :
                            'Delivered';
                        statusText = status;
                    }

                    // Popup content
                    var popupContent = `
                        <div class="custom-popup" style="min-width: 250px;">
                            <h6 style="margin: 0 0 8px 0; color: #1a73e8;">
                                <strong>${report.title}</strong>
                            </h6>
                            <div style="font-size: 13px; line-height: 1.5;">
                                <p style="margin: 4px 0;">
                                    <strong>Status:</strong>
                                    <span style="
                                        background: ${color};
                                        color: white;
                                        padding: 2px 8px;
                                        border-radius: 12px;
                                        font-size: 11px;
                                    ">${statusText}</span>
                                </p>
                                <p style="margin: 4px 0;">
                                    <strong>Kategori:</strong> ${report.report_category?.name || '-'}
                                </p>
                                <p style="margin: 4px 0;">
                                    <strong>Tanggal:</strong>
                                    ${new Date(report.created_at).toLocaleDateString('id-ID')}
                                </p>
                                <p style="margin: 4px 0;">
                                    <strong>Deskripsi:</strong> ${report.description}
                                </p>
                            </div>
                            ${report.image ? `
                                        <img src="/storage/${report.image}"
                                             style="width: 100%; margin-top: 8px; border-radius: 4px;">
                                    ` : ''}
                            <a href="/admin/report/${report.id}"
                               style="
                                   display: block;
                                   margin-top: 8px;
                                   padding: 6px;
                                   background: #1a73e8;
                                   color: white;
                                   text-align: center;
                                   text-decoration: none;
                                   border-radius: 4px;
                                   font-size: 13px;
                               ">
                                Lihat Detail
                            </a>
                        </div>
                    `;

                    L.marker([report.latitude, report.longitude], {
                            icon: customIcon
                        })
                        .addTo(map)
                        .bindPopup(popupContent);
                }
            });

            // Ganti bagian ini di script Anda:
            // Boundary Desa Klayan (optional)
            var klayanBounds = [
                [-6.70478, 108.52841], // Southwest
                [-6.70478, 108.56841], // Southeast
                [-6.66478, 108.56841], // Northeast
                [-6.66478, 108.52841], // Northwest
                [-6.70478, 108.52841] // Close polygon
            ];

            L.polygon(klayanBounds, {
                color: '#FF5252',
                weight: 2,
                opacity: 1,
                fillColor: '#FF5252',
                fillOpacity: 0.08,
                dashArray: '8, 4'
            }).addTo(map);

            // Legend
            var legend = L.control({
                position: 'bottomright'
            });
            legend.onAdd = function() {
                var div = L.DomUtil.create('div', 'info legend');
                var statuses = [{
                        label: 'Pending',
                        color: '#FFA500'
                    },
                    {
                        label: 'Terkirim',
                        color: '#2196F3'
                    },
                    {
                        label: 'Proses',
                        color: '#FFC107'
                    },
                    {
                        label: 'Selesai',
                        color: '#4CAF50'
                    },
                    {
                        label: 'Ditolak',
                        color: '#F44336'
                    }
                ];

                div.innerHTML = '<h6 style="margin: 0 0 8px;">Status</h6>';
                statuses.forEach(s => {
                    div.innerHTML += `
                        <div style="margin-bottom: 4px;">
                            <span style="
                                display: inline-block;
                                width: 12px;
                                height: 12px;
                                background: ${s.color};
                                border-radius: 50%;
                                margin-right: 6px;
                            "></span>
                            ${s.label}
                        </div>
                    `;
                });
                return div;
            };
            legend.addTo(map);
        });
    </script>
@endsection
