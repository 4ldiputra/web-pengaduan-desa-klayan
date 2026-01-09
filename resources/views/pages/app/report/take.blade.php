@extends('layouts.no-nav')

@section('title', 'Ambil Foto')

@section('content')
    <div class="d-flex flex-column justify-content-center align-items-center">
        <video autoplay="true" id="video-webcam" style="max-width: 100%; max-height: 400px; border: 2px solid #ddd;">
            Browser tidak mendukung. silahkan upgrade ke versi terbaru.
        </video>

        <div class="mt-3">
            <label for="file-upload" class="btn btn-outline-secondary">
                <i class="fas fa-upload"></i> Upload Gambar
            </label>
            <input id="file-upload" type="file" accept="image/*" style="display: none;" onchange="handleFileUpload(this)">
        </div>

        <div class="d-flex justify-content-center mt-3 position-absolute bottom-0 w-100 px-3 pb-4">
            <button class="btn btn-primary me-2" onclick="takeSnapshot()">
                <i class="fas fa-camera"></i> Ambil Foto
            </button>
            <button class="btn btn-outline-secondary ms-2" onclick="switchCamera()" id="switch-camera-btn"
                title="Ganti Kamera">
                <i class="fas fa-sync-alt"></i> Ganti Kamera
            </button>
        </div>

        <a href="{{ route('home') }}" class="btn btn-outline-primary mt-4">Kembali</a>
    </div>
@endsection
