@extends('layouts.no-nav')

@section('title', 'Tambah Laporan')

@section('content')
    <h3 class="mb-3">Laporkan segera masalahmu di sini!</h3>

    <p class="text-description">Isi form dibawah ini dengan baik dan benar sehingga kami dapat memvalidasi dan
        menangani
        laporan anda
        secepatnya</p>

    <form action="{{route('report.store')}}" method="POST" class="mt-4" enctype="multipart/form-data">
        @csrf
        <input type="hidden" id="lat" name="latitude">
        <input type="hidden" id="lng" name="longitude">

        <div class="mb-3">
            <label for="title" class="form-label">Judul Laporan</label>
            <input type="text" class="form-control @error('title') is-invalid @enderror" id="title" name="title"
                value="{{ old('title') }}">

            @error('title')
                <div class="invalid-feedback">
                    {{ $message }}
                </div>
            @enderror
        </div>

        <div class="mb-3">
            <label for="report_category_id" class="form-label">Kategori Laporan</label>
                <select name="report_category_id" class="form-control @error('report_category_id') is-invalid @enderror">
                    @foreach ($categories as $category)
                        <option value="{{ $category->id }}" @if (old('report_category_id') == $category->id) selected @endif>
                            {{ $category->name }}
                        </option>
                    @endforeach
                </select>
                @error('report_category_id')
                    <div class="invalid-feedback">
                        {{ $message }}
                    </div>
                @enderror

        </div>

        <div class="mb-3">
            <label for="image" class="form-label">Bukti Laporan</label>
            <input type="file" class="form-control @error('image') is-invalid @enderror" id="image" name="image"
                style="display: none">
            <img alt="image" id="image-preview" class="img-fluid rounded-2 mb-3 border">

            @error('image')
                <div class="invalid-feedback">
                    {{ $message }}
                </div>
            @enderror
        </div>

        <div class="mb-3">
            <label for="description" class="form-label">Ceritakan Laporan Kamu</label>
             <textarea type="text" class="form-control @error('description') is-invalid @enderror" id="description" name="description" value="{{ old('description') }}" rows="5" ></textarea>
            @error('description')
                <div class="invalid-feedback">
                    {{ $message }}
                </div>
            @enderror
        </div>

        <div class="mb-3">
            <label for="map" class="form-label">Lokasi Laporan</label>
            <div id="map"></div>
        </div>

        <div class="mb-3">
            <label for="address" class="form-label">Alamat Lengkap</label>
           <textarea type="text" class="form-control @error('address') is-invalid @enderror" id="address" name="address" value="{{old('address')}}" rows="5" ></textarea>
            @error('address')
                <div class="invalid-feedback">
                    {{ $message }}
                </div>
            @enderror
        </div>

        <button class="btn btn-primary w-100 mt-2" type="submit" color="primary">
            Laporkan
        </button>
    </form>
@endsection

@section('scripts')
    <script>
        // ========================================
        // EXISTING CODE (jangan dihapus)
        // ========================================
        // Ambil base64 dari localStorage
        var imageBase64 = localStorage.getItem('image');

        // Mengubah base64 menjadi binary Blob
        function base64ToBlob(base64, mime) {
            var byteString = atob(base64.split(',')[1]);
            var ab = new ArrayBuffer(byteString.length);
            var ia = new Uint8Array(ab);
            for (var i = 0; i < byteString.length; i++) {
                ia[i] = byteString.charCodeAt(i);
            }
            return new Blob([ab], {
                type: mime
            });
        }

        // Fungsi untuk membuat objek file dan set ke input file
        function setFileInputFromBase64(base64) {
            // Mengubah base64 menjadi Blob
            var blob = base64ToBlob(base64, 'image/jpeg');
            var file = new File([blob], 'image.jpg', {
                type: 'image/jpeg'
            });

            // Set file ke input file
            var imageInput = document.getElementById('image');
            var dataTransfer = new DataTransfer();
            dataTransfer.items.add(file);
            imageInput.files = dataTransfer.files;

            // Menampilkan preview gambar
            var imagePreview = document.getElementById('image-preview');
            imagePreview.src = URL.createObjectURL(file);
        }

        // Set nilai input file dan preview gambar
        setFileInputFromBase64(imageBase64);

        // ========================================
        // ✨ NEW CODE: AI AUTO-PREDICT KATEGORI
        // ========================================
        document.addEventListener('DOMContentLoaded', function() {
            const titleInput = document.getElementById('title');
            const categorySelect = document.querySelector('select[name="report_category_id"]');

            let typingTimer;
            const doneTypingInterval = 1000; // 1 detik setelah user berhenti ngetik

            // Event ketika user ngetik di field judul
            titleInput.addEventListener('keyup', function() {
                clearTimeout(typingTimer);
                const title = this.value.trim();

                // Cek panjang minimal 10 karakter
                if (title.length >= 10) {
                    typingTimer = setTimeout(function() {
                        predictCategory(title);
                    }, doneTypingInterval);
                }
            });

            // Event ketika user tekan key down
            titleInput.addEventListener('keydown', function() {
                clearTimeout(typingTimer);
            });

            // Fungsi untuk panggil API predict
            function predictCategory(title) {
                // Tampilkan loading indicator (optional)
                categorySelect.style.opacity = '0.5';

                // Panggil API
                fetch('{{ route("api.predict.category") }}', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': '{{ csrf_token() }}'
                    },
                    body: JSON.stringify({
                        title: title
                    })
                })
                .then(response => response.json())
                .then(data => {
                    // Kembalikan opacity normal
                    categorySelect.style.opacity = '1';

                    if (data.success) {
                        // Auto-select kategori
                        categorySelect.value = data.category_id;

                        // Tampilkan notifikasi sukses (optional)
                        showNotification('✅ Kategori otomatis terdeteksi: ' + data.category_name, 'success');

                        console.log('AI Prediction:', data);
                    } else {
                        console.log('Prediction failed:', data.message);
                    }
                })
                .catch(error => {
                    // Kembalikan opacity normal
                    categorySelect.style.opacity = '1';

                    console.error('Error:', error);
                    showNotification('⚠️ Gagal memprediksi kategori. Silakan pilih manual.', 'error');
                });
            }

            // Fungsi untuk tampilkan notifikasi
            function showNotification(message, type) {
                // Cek apakah sudah ada notifikasi
                let notification = document.getElementById('ai-notification');

                if (!notification) {
                    // Buat elemen notifikasi baru
                    notification = document.createElement('div');
                    notification.id = 'ai-notification';
                    notification.style.cssText = `
                        position: fixed;
                        top: 20px;
                        right: 20px;
                        padding: 15px 20px;
                        border-radius: 8px;
                        color: white;
                        font-weight: 500;
                        z-index: 9999;
                        box-shadow: 0 4px 6px rgba(0,0,0,0.1);
                        animation: slideIn 0.3s ease-out;
                    `;
                    document.body.appendChild(notification);
                }

                // Set warna berdasarkan type
                if (type === 'success') {
                    notification.style.backgroundColor = '#10b981';
                } else if (type === 'error') {
                    notification.style.backgroundColor = '#ef4444';
                } else {
                    notification.style.backgroundColor = '#3b82f6';
                }

                // Set pesan
                notification.textContent = message;
                notification.style.display = 'block';

                // Auto hide setelah 3 detik
                setTimeout(function() {
                    notification.style.animation = 'slideOut 0.3s ease-out';
                    setTimeout(function() {
                        notification.style.display = 'none';
                    }, 300);
                }, 3000);
            }
        });

        // CSS Animation
        const style = document.createElement('style');
        style.textContent = `
            @keyframes slideIn {
                from {
                    transform: translateX(100%);
                    opacity: 0;
                }
                to {
                    transform: translateX(0);
                    opacity: 1;
                }
            }
            @keyframes slideOut {
                from {
                    transform: translateX(0);
                    opacity: 1;
                }
                to {
                    transform: translateX(100%);
                    opacity: 0;
                }
            }
        `;
        document.head.appendChild(style);
    </script>
@endsection
