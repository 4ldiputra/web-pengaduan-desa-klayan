// public/js/take.js

var video = document.querySelector("#video-webcam");
var currentStream = null;
var isFrontCamera = true;
var cameraDevices = [];

// Mulai kamera dengan kamera depan atau belakang

async function startCamera() {
    if (currentStream) {
        currentStream.getTracks().forEach(track => track.stop());
    }

    try {
        const devices = await navigator.mediaDevices.enumerateDevices();
        cameraDevices = devices.filter(device => device.kind === 'videoinput');

        if (cameraDevices.length === 0) {
            throw new Error("Tidak ada kamera yang ditemukan.");
        }

        if (!isFrontCamera && cameraDevices.length === 1) {
            alert("Perangkat ini hanya memiliki satu kamera. Tidak bisa ganti ke kamera belakang.");
            isFrontCamera = true;
        }

        let deviceId = null;
        if (isFrontCamera) {
            const frontCam = cameraDevices.find(d =>
                d.label.toLowerCase().includes('front') || d.label.toLowerCase().includes('depan')
            );
            deviceId = frontCam ? frontCam.deviceId : cameraDevices[0].deviceId;
        } else {
            const backCam = cameraDevices.find(d =>
                d.label.toLowerCase().includes('back') || d.label.toLowerCase().includes('belakang')
            );
            deviceId = backCam ? backCam.deviceId : cameraDevices[cameraDevices.length - 1]?.deviceId || cameraDevices[0].deviceId;
        }

        const stream = await navigator.mediaDevices.getUserMedia({
            video: { deviceId: deviceId ? { exact: deviceId } : undefined }
        });
        currentStream = stream;
        video.srcObject = stream;

        // Optional: update title tooltip
        const switchBtn = document.getElementById('switch-camera-btn');
        if (switchBtn) {
            switchBtn.title = isFrontCamera ? "Ganti ke Kamera Belakang" : "Ganti ke Kamera Depan";
        }

    } catch (err) {
        alert(`Gagal akses kamera: ${err.message}`);
        isFrontCamera = true;
    }
}

function switchCamera() {
    isFrontCamera = !isFrontCamera;
    startCamera();
}

function handleFileUpload(input) {
    if (input.files?.[0]) {
        const reader = new FileReader();
        reader.onload = (e) => {
            localStorage.setItem('image', e.target.result);
            window.location.href = '/preview';
        };
        reader.readAsDataURL(input.files[0]);
    }
}

function takeSnapshot() {
    if (!video?.videoWidth || !video?.videoHeight) {
        alert("Belum ada video stream. Pastikan kamera sudah aktif!");
        return;
    }

    const canvas = document.createElement('canvas');
    const ctx = canvas.getContext('2d');
    canvas.width = video.videoWidth;
    canvas.height = video.videoHeight;
    ctx.drawImage(video, 0, 0);
    localStorage.setItem('image', canvas.toDataURL('image/png'));
    window.location.href = '/preview';
}

// Jalankan saat DOM siap
document.addEventListener('DOMContentLoaded', () => {
    if (document.getElementById('video-webcam')) {
        startCamera();
    }
});
