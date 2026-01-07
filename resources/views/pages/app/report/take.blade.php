@extends('layouts.no-nav')

@section('title', 'Ambil Foto')

@section('content')
    <div class="d-flex flex-column justify-content-center align-items-center">
        <!-- Video Stream -->
        <video autoplay="true" id="video-webcam" style="max-width: 100%; max-height: 400px; border: 2px solid #ddd;">
            Browsermu tidak mendukung bro, upgrade donk!
        </video>

        <!-- Tombol Upload Gambar -->
        <div class="mt-3">
            <label for="file-upload" class="btn btn-outline-secondary">
                <i class="fas fa-upload"></i> Upload Gambar
            </label>
            <input id="file-upload" type="file" accept="image/*" style="display: none;" onchange="handleFileUpload(this)">
        </div>

        <!-- Tombol Ambil Foto -->
        <div class="d-flex justify-content-center mt-3 position-absolute bottom-0">
            <button class="btn btn-primary btn-snap mb-3 me-2" onclick="takeSnapshot()">
                <i class="fas fa-camera"></i> Ambil Foto
            </button>
            <!-- Tombol Ganti Kamera (opsional) -->
            <button class="btn btn-outline-secondary btn-snap mb-3" onclick="switchCamera()" id="switch-camera-btn">
                <i class="fas fa-sync-alt"></i> Ganti Kamera
            </button>
        </div>

        <a href="{{ route('home') }}" class="btn btn-outline-primary mt-4">
            Kembali
        </a>
    </div>

    <script>
        var video = document.querySelector("#video-webcam");
        var currentStream = null;
        var isFrontCamera = true; // Default: kamera depan

        navigator.getUserMedia = navigator.getUserMedia || navigator.webkitGetUserMedia || navigator.mozGetUserMedia ||
            navigator.msGetUserMedia || navigator.oGetUserMedia;

        function startCamera() {
            if (currentStream) {
                currentStream.getTracks().forEach(track => track.stop());
            }

            const constraints = {
                video: {
                    facingMode: isFrontCamera ? "user" : "environment" // "user"=depan, "environment"=belakang
                }
            };

            navigator.mediaDevices.getUserMedia(constraints)
                .then(function(stream) {
                    currentStream = stream;
                    video.srcObject = stream;
                })
                .catch(function(err) {
                    alert("Izinkan menggunakan webcam untuk demo! Error: " + err.message);
                });
        }

        function switchCamera() {
            isFrontCamera = !isFrontCamera;
            startCamera();
            document.getElementById('switch-camera-btn').innerHTML = `<i class="fas fa-sync-alt"></i> ${isFrontCamera ? 'Ganti Kamera Belakang' : 'Ganti Kamera Depan'}`;
        }

        function handleFileUpload(input) {
            if (input.files && input.files[0]) {
                var reader = new FileReader();
                reader.onload = function(e) {
                    localStorage.setItem('image', e.target.result);
                    window.location.href = '/preview';
                };
                reader.readAsDataURL(input.files[0]);
            }
        }

        function takeSnapshot() {
            var canvas = document.createElement('canvas');
            var context = canvas.getContext('2d');
            var video = document.getElementById('video-webcam');
            canvas.width = video.videoWidth;
            canvas.height = video.videoHeight;
            context.drawImage(video, 0, 0);
            var dataURL = canvas.toDataURL('image/png');
            localStorage.setItem('image', dataURL);

            window.location.href = '/preview';
        }

        // Start camera when page loads
        document.addEventListener('DOMContentLoaded', startCamera);
    </script>
@endsection
