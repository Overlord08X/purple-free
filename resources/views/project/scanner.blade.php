@extends('layouts.app')
<meta name="csrf-token" content="{{ csrf_token() }}">
@section('title', 'Barcode Scanner')

@section('content')

<div class="main-panel">
    <div class="content-wrapper">

        <div class="page-header">
            <h3 class="page-title">
                <span class="page-title-icon bg-gradient-primary text-white me-2">
                    <i class="mdi mdi-barcode"></i>
                </span>
                Barcode Scanner
            </h3>
        </div>

        <div class="card">
            <div class="card-body">
                <h4 class="card-title">Pemindai Barcode & QR Code</h4>

                <!-- Instruksi Camera Permission -->
                <div id="permissionAlert" class="alert alert-info" role="alert" style="display: none;">
                    <h5 class="alert-heading"><i class="mdi mdi-information"></i> Izin Kamera Diperlukan</h5>
                    <hr>
                    <p><strong>Untuk menggunakan scanner, ikuti langkah berikut:</strong></p>
                    <ol>
                        <li>Klik tombol <strong>"Mulai Scan"</strong></li>
                        <li>Browser akan menampilkan popup meminta akses kamera</li>
                        <li>Klik <strong>"Izinkan"</strong> atau <strong>"Allow"</strong></li>
                        <li>Arahkan kamera ke barcode/QR code</li>
                    </ol>
                    <p class="mb-0"><small><strong>Browser Chrome:</strong> Popup akan muncul di atas. | <strong>Browser Firefox:</strong> Popup di bawah address bar. | <strong>Browser Safari:</strong> Popup di tengah layar.</small></p>
                </div>

                <!-- Camera Not Supported Warning -->
                <div id="cameraNotSupportedAlert" class="alert alert-warning" role="alert" style="display: none;">
                    <h5 class="alert-heading"><i class="mdi mdi-alert"></i> Kamera Bermasalah</h5>
                    <p id="cameraErrorMessage">Perangkat Anda tidak memiliki kamera atau browser tidak mendukung akses kamera.</p>
                    <hr>
                    <p><strong>Solusi:</strong></p>
                    <ul>
                        <li><strong>Jika kamera sedang digunakan aplikasi lain:</strong> Tutup Zoom, Microsoft Teams, atau aplikasi video call lainnya</li>
                        <li><strong>Jika sistem meminta izin terlebih dahulu:</strong> Buka System Settings → Privacy → Camera, pastikan aplikasi browser diizinkan</li>
                        <li><strong>Jika browser tidak mendukung:</strong> Gunakan Chrome, Firefox, Safari terbaru, atau Edge</li>
                        <li><strong>Akses halaman melalui HTTPS atau localhost</strong></li>
                    </ul>
                    <p class="mb-0"><button id="btnRetryCamera" class="btn btn-sm btn-warning">🔄 Coba Lagi</button></p>
                </div>

                <!-- Permission Denied Warning -->
                <div id="permissionDeniedAlert" class="alert alert-danger" role="alert" style="display: none;">
                    <h5 class="alert-heading"><i class="mdi mdi-close-circle"></i> Akses Kamera Ditolak</h5>
                    <hr>
                    <p><strong>Cara for mengizinkan akses kamera:</strong></p>
                    <p><strong>Google Chrome:</strong></p>
                    <ul>
                        <li>Klik ikon kunci/setting di address bar</li>
                        <li>Pilih "Kamera"</li>
                        <li>Ubah dari "Blokir" menjadi "Izinkan"</li>
                    </ul>
                    <p><strong>Firefox:</strong></p>
                    <ul>
                        <li>Klik ikon kebijakan di address bar</li>
                        <li>Pilih "Kelola" untuk Kamera</li>
                        <li>Ubah menjadi "Izinkan"</li>
                    </ul>
                    <p class="mb-0"><button id="btnRetryPermission" class="btn btn-sm btn-warning">Coba Lagi</button></p>
                </div>

                <div class="row mb-3">
                    <div class="col-md-12">
                        <div id="reader" style="width: 100%; max-width: 800px; margin: 0 auto; border: 2px solid #ddd; border-radius: 8px;"></div>
                    </div>
                </div>

                <div class="row mb-3">
                    <div class="col-md-12">
                        <button id="btnStartScan" class="btn btn-gradient-primary me-2">
                            <i class="mdi mdi-play"></i> Mulai Scan
                        </button>
                        <button id="btnStopScan" class="btn btn-gradient-danger" style="display: none;">
                            <i class="mdi mdi-stop"></i> Hentikan Scan
                        </button>
        <button id="btnResetResult" class="btn btn-gradient-secondary" style="display: none;">
                            <i class="mdi mdi-refresh"></i> Reset Hasil
                        </button>
                    </div>
                </div>

                <!-- Status Camera -->
                <div id="cameraStatusContainer" class="mb-3">
                    <small id="cameraStatus" class="text-muted">
                        <i class="mdi mdi-loading mdi-spin"></i> Memeriksa ketersediaan kamera...
                    </small>
                </div>

                <!-- Hasil Scan -->
                <div id="resultContainer" style="display: none;">
                    <div class="alert alert-success" role="alert">
                        <h4 class="alert-heading">✓ Barcode Berhasil Dipindai!</h4>
                        <hr>
                        <div class="row">
                            <div class="col-md-3">
                                <p><strong>ID Barang:</strong></p>
                                <p id="resultIdBarang" style="font-size: 1.5em; font-weight: bold; color: #28a745;">-</p>
                            </div>
                            <div class="col-md-5">
                                <p><strong>Nama Barang:</strong></p>
                                <p id="resultNamaBarang" style="font-size: 1.2em; color: #333;">-</p>
                            </div>
                            <div class="col-md-4">
                                <p><strong>Harga Barang:</strong></p>
                                <p id="resultHargaBarang" style="font-size: 1.2em; font-weight: bold; color: #ff6b6b;">Rp. -</p>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Error Message -->
                <div id="errorContainer" style="display: none;">
                    <div class="alert alert-danger" role="alert">
                        <h4 class="alert-heading">✗ Barang Tidak Ditemukan</h4>
                        <p id="errorMessage">Barang dengan kode tersebut tidak ditemukan di database.</p>
                    </div>
                </div>

            </div>
        </div>
    </div>

<!-- Audio untuk beep sound -->
<audio id="beepSound" preload="auto">
    <source src="{{ asset('assets/sound/dragon-studio-censor-beep-3-372460.mp3') }}" type="audio/mpeg">
</audio>

<script src="https://unpkg.com/html5-qrcode@2.3.8/html5-qrcode.min.js"></script>
<script>
document.addEventListener('DOMContentLoaded', function() {
    let htmlQrcodeScanner = null;
    let isScanning = false;

    const readerElement = document.getElementById('reader');
    const btnStartScan = document.getElementById('btnStartScan');
    const btnStopScan = document.getElementById('btnStopScan');
    const btnResetResult = document.getElementById('btnResetResult');
    const btnRetryPermission = document.getElementById('btnRetryPermission');
    const resultContainer = document.getElementById('resultContainer');
    const errorContainer = document.getElementById('errorContainer');
    const permissionAlert = document.getElementById('permissionAlert');
    const cameraNotSupportedAlert = document.getElementById('cameraNotSupportedAlert');
    const permissionDeniedAlert = document.getElementById('permissionDeniedAlert');
    const cameraStatusContainer = document.getElementById('cameraStatusContainer');
    const cameraStatus = document.getElementById('cameraStatus');
    const beepSound = document.getElementById('beepSound');

    // Hide alerts initially
    permissionAlert.style.display = 'block';
    cameraNotSupportedAlert.style.display = 'none';
    permissionDeniedAlert.style.display = 'none';
    cameraStatus.innerHTML = '<i class="mdi mdi-check-circle text-success"></i> <strong>Kamera siap digunakan</strong> - Klik "Mulai Scan"';
    btnStartScan.disabled = false;

    // Check if getUserMedia is supported
    if (!navigator.mediaDevices || !navigator.mediaDevices.getUserMedia) {
        showCameraNotSupported('Browser Anda tidak mendukung WebRTC. Gunakan Chrome, Firefox, Safari, atau Edge terbaru.');
    }

    // Retry permission button
    if (btnRetryPermission) {
        btnRetryPermission.addEventListener('click', function() {
            permissionDeniedAlert.style.display = 'none';
            permissionAlert.style.display = 'block';
            cameraStatus.innerHTML = '<i class="mdi mdi-check-circle text-success"></i> <strong>Kamera siap digunakan</strong> - Klik "Mulai Scan"';
            btnStartScan.disabled = false;
        });
    }

    // Retry camera button (for not supported alert)
    const btnRetryCamera = document.getElementById('btnRetryCamera');
    if (btnRetryCamera) {
        btnRetryCamera.addEventListener('click', function() {
            cameraNotSupportedAlert.style.display = 'none';
            permissionAlert.style.display = 'block';
            cameraStatus.innerHTML = '<i class="mdi mdi-check-circle text-success"></i> <strong>Kamera siap digunakan</strong> - Klik "Mulai Scan"';
            btnStartScan.disabled = false;
        });
    }

    function showCameraNotSupported(message) {
        cameraStatus.innerHTML = '<i class="mdi mdi-alert-circle text-danger"></i> <strong>Kamera bermasalah</strong>';
        const errorElement = document.getElementById('cameraErrorMessage');
        if (errorElement) {
            errorElement.textContent = message;
        }
        cameraNotSupportedAlert.style.display = 'block';
        permissionAlert.style.display = 'none';
        permissionDeniedAlert.style.display = 'none';
        btnStartScan.disabled = true;
    }

    function handlePermissionDenied(error) {
        console.error('Error akses kamera:', error);
        cameraStatus.innerHTML = '<i class="mdi mdi-close-circle text-danger"></i> <strong>Akses kamera ditolak</strong>';
        permissionDeniedAlert.style.display = 'block';
        permissionAlert.style.display = 'none';
        cameraNotSupportedAlert.style.display = 'none';
        btnStartScan.disabled = false; // Allow retry
    }

    // Start Scanning
    btnStartScan.addEventListener('click', function() {
        if (!htmlQrcodeScanner) {
            htmlQrcodeScanner = new Html5Qrcode("reader");
        }

        btnStartScan.disabled = true;
        cameraStatus.innerHTML = '<i class="mdi mdi-loading mdi-spin"></i> <strong>Mengakses kamera...</strong>';

        htmlQrcodeScanner.start(
            { facingMode: "environment" },
            {
                fps: 10,
                qrbox: { width: 250, height: 250 }
            },
            onScanSuccess,
            onScanError
        ).then(() => {
            isScanning = true;
            btnStartScan.style.display = 'none';
            btnStopScan.style.display = 'inline-block';
            resultContainer.style.display = 'none';
            errorContainer.style.display = 'none';
            cameraStatus.innerHTML = '<i class="mdi mdi-video text-success"></i> <strong>Scanning aktif...</strong>';
            permissionAlert.style.display = 'none';
            cameraNotSupportedAlert.style.display = 'none';
            permissionDeniedAlert.style.display = 'none';
        }).catch(err => {
            console.error('Failed to start scanning:', err);
            btnStartScan.disabled = false;
            
            const errorMsg = err.toString().toLowerCase();
            
            if (errorMsg.includes('notallowederror') || 
                errorMsg.includes('permission denied') || 
                errorMsg.includes('permission was denied')) {
                handlePermissionDenied(err);
            } else if (errorMsg.includes('notfounderror') || 
                       errorMsg.includes('no cameras found') ||
                       errorMsg.includes('requested device not found')) {
                showCameraNotSupported('Kamera tidak terdeteksi. Pastikan: 1) Kamera terhubung; 2) Tidak digunakan aplikasi lain (Zoom, Teams, dll)');
            } else if (errorMsg.includes('notsupportederror') ||
                       errorMsg.includes('not supported')) {
                showCameraNotSupported('Browser Anda tidak mendukung akses kamera.');
            } else if (errorMsg.includes('notreadableerror') || 
                       errorMsg.includes('cannot access')) {
                showCameraNotSupported('Kamera sedang digunakan oleh aplikasi lain. Tutup aplikasi lain terlebih dahulu (Zoom, Teams, dll)');
            } else {
                showCameraNotSupported('Error: ' + err.message);
            }
        });
    });

    // Stop Scanning
    btnStopScan.addEventListener('click', function() {
        if (htmlQrcodeScanner && isScanning) {
            htmlQrcodeScanner.stop().then(() => {
                isScanning = false;
                btnStartScan.style.display = 'inline-block';
                btnStopScan.style.display = 'none';
                cameraStatus.innerHTML = '<i class="mdi mdi-check-circle text-success"></i> <strong>Kamera tersedia</strong> - Scan dihentikan';
            }).catch(err => {
                console.error('Failed to stop scanning:', err);
            });
        }
    });

    // Reset Result
    btnResetResult.addEventListener('click', function() {
        resultContainer.style.display = 'none';
        errorContainer.style.display = 'none';
        btnResetResult.style.display = 'none';
        btnStartScan.style.display = 'inline-block';
        cameraStatus.innerHTML = '<i class="mdi mdi-check-circle text-success"></i> <strong>Kamera tersedia</strong> - Klik "Mulai Scan" untuk memulai';
        
        // Restart scanning
        if (htmlQrcodeScanner && !isScanning) {
            htmlQrcodeScanner.start(
                { facingMode: "environment" },
                {
                    fps: 10,
                    qrbox: { width: 250, height: 250 }
                },
                onScanSuccess,
                onScanError
            ).then(() => {
                isScanning = true;
                btnStartScan.style.display = 'none';
                btnStopScan.style.display = 'inline-block';
                cameraStatus.innerHTML = '<i class="mdi mdi-video text-success"></i> <strong>Scanning aktif...</strong>';
            });
        }
    });

    // On Successful Scan
    function onScanSuccess(decodedText, decodedResult) {
        console.log('Scan result:', decodedText);

        // Stop scanning
        if (htmlQrcodeScanner && isScanning) {
            htmlQrcodeScanner.stop().then(() => {
                isScanning = false;
                btnStartScan.style.display = 'none';
                btnStopScan.style.display = 'none';
                btnResetResult.style.display = 'inline-block';
            });
        }

        // Play beep sound
        playBeep();

        // Fetch barang data from server
        fetchBarangData(decodedText);
    }

    // On Scan Error
    function onScanError(errorMessage) {
        // Silently ignore scanning errors, they happen frequently during scanning
        // console.log('Scan error:', errorMessage);
    }

    // Fetch Barang Data
    function fetchBarangData(idBarang) {
        fetch(`{{ route('project.index') }}/../barang/${idBarang}`)
            .then(response => {
                if (!response.ok) {
                    throw new Error('Barang not found');
                }
                return response.json();
            })
            .then(data => {
                if (data && data.idbarang) {
                    const namaBarang = data.nama_barang ?? data.namabarang ?? 'N/A';
                    const hargaBarang = data.harga_barang ?? data.hargabarang ?? 0;

                    // Display success result
                    document.getElementById('resultIdBarang').textContent = data.idbarang;
                    document.getElementById('resultNamaBarang').textContent = namaBarang;
                    document.getElementById('resultHargaBarang').textContent = 'Rp. ' + new Intl.NumberFormat('id-ID').format(hargaBarang);
                    
                    resultContainer.style.display = 'block';
                    errorContainer.style.display = 'none';
                } else {
                    showError('Data barang tidak valid');
                }
            })
            .catch(error => {
                console.error('Error:', error);
                showError('Barang dengan kode ' + idBarang + ' tidak ditemukan');
            });
    }

    // Show Error Message
    function showError(message) {
        document.getElementById('errorMessage').textContent = message;
        errorContainer.style.display = 'block';
        resultContainer.style.display = 'none';
    }

    // Play Beep Sound
    function playBeep() {
        // Create a new audio context for the beep
        const audioContext = new (window.AudioContext || window.webkitAudioContext)();
        const oscillator = audioContext.createOscillator();
        const gainNode = audioContext.createGain();

        oscillator.connect(gainNode);
        gainNode.connect(audioContext.destination);

        // Set frequency and duration
        oscillator.frequency.value = 800; // 800 Hz beep
        oscillator.type = 'sine';

        gainNode.gain.setValueAtTime(0.3, audioContext.currentTime);
        gainNode.gain.exponentialRampToValueAtTime(0.01, audioContext.currentTime + 0.1);

        oscillator.start(audioContext.currentTime);
        oscillator.stop(audioContext.currentTime + 0.1);

        // Alternatively, you can play the provided sound file:
        // beepSound.currentTime = 0;
        // beepSound.play().catch(err => console.log('Could not play sound:', err));
    }
});
</script>

@endsection
