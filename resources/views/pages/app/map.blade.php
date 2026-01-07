<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Desa Klayan</title>

    <!-- Favicon -->
    <link rel="icon" type="image/png" href="/images/logodesa.png">

    <!-- Bootstrap 5 CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">

    <!-- Leaflet CSS & JS -->
    <link rel="stylesheet" href="https://unpkg.com/leaflet/dist/leaflet.css" />
    <script src="https://unpkg.com/leaflet/dist/leaflet.js"></script>

    <!-- Animate.css -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/animate.css/4.1.1/animate.min.css" />
</head>

<body>
    <div class="landing-page">

        <!-- Navbar -->
        <nav class="navbar navbar-expand-lg navbar-light shadow-sm" style="background-color: transparent !important;">
            <div class="container">
                <a class="navbar-brand d-flex align-items-center" href="#">
                    <img src="/images/logodesa.png" alt="Logo Desa Klayan" height="80" class="me-2">
                    <span class="navbar-brand-text fs-4 fw-bold text-dark">Desa Klayan</span>
                </a>

                <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
                    <span class="navbar-toggler-icon"></span>
                </button>

                <div class="collapse navbar-collapse justify-content-end" id="navbarNav">
                    <ul class="navbar-nav align-items-lg-center">
                        <li class="nav-item me-2">
                            <a class="nav-link" href="#">Home</a>
                        </li>
                        <li class="nav-item me-2">
                            <a class="nav-link" href="#mapping">Peta</a>
                        </li>
                        <li class="nav-item me-2">
                            <a class="nav-link" href="#tentang">Tentang</a>
                        </li>
                        <li class="nav-item">
                            <a href="{{ route('login') }}" class="btn btn-primary">
                                <i class="bi bi-box-arrow-in-right"></i> Buat Laporan
                            </a>
                        </li>
                    </ul>
                </div>
            </div>
        </nav>

        <!-- Hero Section -->
        <section class="hero d-flex flex-column justify-content-center align-items-center text-white text-center"
            style="height:100vh; background: url('/images/image.png') center/cover no-repeat; position:relative;">
            <div style="position:absolute; top:0; left:0; width:100%; height:100%; background: rgba(0,0,0,0.5);"></div>
            <div class="hero-content" style="position:relative; z-index:2;">
                <h1 class="display-5 fw-bold mb-3 animate__animated animate__fadeInDown">Selamat Datang Di Website Desa
                    Klayan</h1>
                <p class="lead mb-4 animate__animated animate__fadeInUp animate__delay-1s">
                    Pantau laporan masyarakat secara real-time di wilayah Anda
                </p>
                <a href="{{ route('login') }}"
                    class="btn btn-lg btn-primary animate__animated animate__pulse animate__infinite animate__delay-2s">
                    <i class="bi bi-box-arrow-in-right"></i> Buat Laporan
                </a>
            </div>
        </section>

        <!-- Map Section -->
        <section class="map-section py-5" style="background-color:#f8f9fa;">
            <div class="container">
                <h3 class="text-center mb-4" id="mapping">Peta Laporan</h3>
                <div class="card shadow" style="border-radius:1rem; overflow:hidden;">
                    <div class="card-body p-0" style="height:500px;">
                        <div id="map" style="height:100%; width:100%;"></div>
                    </div>
                </div>
            </div>
        </section>

        <!-- Info Section -->
        <section class="info-section text-center py-5 bg-light">
            <div class="container">
                <h3 class="mb-3" id="tentang">Tentang Layanan Ini</h3>
                <h5 class="text-muted mb-0">
                    Sistem ini memudahkan masyarakat dalam melaporkan kejadian dan memantau status laporan secara
                    transparan. Seluruh laporan akan ditampilkan di peta secara real-time.
                </h5>
            </div>
        </section>

        <!-- Footer -->
        <footer class="text-center text-white py-4" style="background-color: #003366;">
            <div class="container">
                <img src="/images/logodesa.png" alt="Logo PJU" height="50" class="d-block mx-auto mb-2 p-1"
                    style="background-color: white; border-radius: 8px;">
                <p class="mb-1">&copy; {{ date('Y') }} Desa Klayan. Semua Hak Dilindungi.</p>
                <small>
                    Dibuat dengan ❤️ menggunakan
                    <a href="https://leafletjs.com/" target="_blank" class="text-white fw-bold">Leaflet</a> &
                    <a href="https://www.openstreetmap.org/" target="_blank"
                        class="text-white fw-bold">OpenStreetMap</a>
                </small>
            </div>
        </footer>

        <script>
            document.addEventListener("DOMContentLoaded", function() {
                // Inisialisasi map (center akan di-set ulang setelah polygon dibuat)
                var map = L.map('map').setView([-6.68478, 108.54841], 14);

                L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
                    maxZoom: 19,
                }).addTo(map);

                // Polygon boundary untuk Desa Klayan berdasarkan data topografi resmi
                // Bounding box: -6.70478 to -6.66478 (lat), 108.52841 to 108.56841 (lon)
                var klayan_boundary = [
                    [-6.70478, 108.52841], // Southwest
                    [-6.70478, 108.56841], // Southeast
                    [-6.66478, 108.56841], // Northeast
                    [-6.66478, 108.52841], // Northwest
                    [-6.70478, 108.52841] // Close polygon
                ];

                L.polygon(klayan_boundary, {
                    color: '#FF6B6B',
                    weight: 2,
                    opacity: 0.8,
                    dashArray: '10, 10',
                    fillColor: '#FF6B6B',
                    fillOpacity: 0.1
                }).addTo(map).bindPopup(
                    '<strong>Desa Klayan</strong><br>Kecamatan Cirebon<br>Elevasi: 3m (rata-rata)');

                // Set center map ke tengah-tengah Desa Klayan
                var centerLat = (-6.70478 + -6.66478) / 2;
                var centerLon = (108.52841 + 108.56841) / 2;
                map.setView([centerLat, centerLon], 14);

                var reports = @json($reports);

                function getMarkerColor(status) {
                    status = status.toLowerCase();
                    if (status.includes("in process") || status.includes("in_process")) return "yellow";
                    if (status.includes("completed") || status.includes("selesai")) return "green";
                    if (status.includes("delivered") || status.includes("terkirim")) return "blue";
                    if (status.includes("rejected") || status.includes("ditolak")) return "red";
                    return "gray";
                }

                reports.forEach(function(report) {
                    if (report.latitude && report.longitude) {
                        // PERBAIKAN: Ambil status TERAKHIR, bukan yang pertama
                        let latestStatus = report.report_statuses?.length ?
                            report.report_statuses[report.report_statuses.length - 1].status :
                            'in_process';

                        let statusLower = latestStatus.toLowerCase();
                        let color = getMarkerColor(statusLower);

                        // Set badge class berdasarkan status
                        let badgeClass = "badge bg-secondary";
                        if (statusLower.includes("in process") || statusLower.includes("in_process")) {
                            badgeClass = "badge bg-warning";
                        } else if (statusLower.includes("completed") || statusLower.includes("selesai")) {
                            badgeClass = "badge bg-success";
                        } else if (statusLower.includes("delivered") || statusLower.includes("terkirim")) {
                            badgeClass = "badge bg-info";
                        } else if (statusLower.includes("rejected") || statusLower.includes("ditolak")) {
                            badgeClass = "badge bg-danger";
                        }

                        var popupContent = `
                            <div style="min-width:250px; max-width:300px;">
                                <h6><strong>Judul Laporan:</strong> ${report.title}</h6>
                                <p><strong>Kategori:</strong> ${report.report_category?.name || '-'}</p>
                                <p><strong>Tanggal:</strong> ${new Date(report.created_at).toLocaleString('id-ID')}</p>
                                <p><strong>Deskripsi:</strong> ${report.description}</p>
                                <p><strong>Status:</strong> <span class="${badgeClass}">${latestStatus}</span></p>
                                ${report.image ? `<img src="/storage/${report.image}" alt="Foto Laporan"
                                                     style="width:100%;max-height:200px;object-fit:cover;"
                                                     class="mt-2 rounded">` : ''}
                            </div>
                        `;

                        L.circleMarker([report.latitude, report.longitude], {
                                radius: 10,
                                fillColor: color,
                                color: "#000",
                                weight: 1,
                                opacity: 1,
                                fillOpacity: 0.8
                            })
                            .addTo(map)
                            .bindPopup(popupContent, {
                                maxWidth: 350
                            });
                    }
                });

                // Legend yang sama dengan admin
                var legend = L.control({
                    position: 'bottomright'
                });

                legend.onAdd = function() {
                    var div = L.DomUtil.create('div', 'info legend shadow p-2 rounded bg-white');
                    var statuses = [{
                            label: "Terkirim",
                            color: "blue"
                        }, // Delivered (Approved)
                        {
                            label: "Proses",
                            color: "yellow"
                        }, // In Process
                        {
                            label: "Selesai",
                            color: "green"
                        }, // Completed
                    ];

                    div.innerHTML = "<h6 class='mb-2'>Keterangan</h6>";
                    statuses.forEach(s => {
                        div.innerHTML += `
            <div class="d-flex align-items-center mb-1">
                <span style="display:inline-block;width:15px;height:15px;background:${s.color};
                       margin-right:8px;border:1px solid #000;border-radius:50%;"></span>
                ${s.label}
            </div>
        `;
                    });
                    return div;
                };

                legend.addTo(map);
            });
        </script>
    </div>

    <!-- Bootstrap JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
</body>

</html>
