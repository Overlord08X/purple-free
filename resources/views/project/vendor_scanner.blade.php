@extends('layouts.app')

@section('title', 'Vendor Scanner')

@section('content')
<div class="main-panel">
    <div class="content-wrapper">
        <div class="page-header">
            <h3 class="page-title">
                <span class="page-title-icon bg-gradient-primary text-white me-2">
                    <i class="mdi mdi-barcode-scan"></i>
                </span>
                Vendor Scanner
            </h3>
        </div>

        <div class="card mb-3">
            <div class="card-body">
                <div class="row g-3 align-items-end">
                    <div class="col-md-5">
                        <label class="form-label">Pilih Vendor</label>
                        <select id="vendorSelect" class="form-select">
                            @foreach($vendors as $vendor)
                                <option value="{{ $vendor->idvendor }}">{{ $vendor->nama_vendor }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-7">
                        <div class="alert alert-info mb-0">
                            Arahkan kamera ke QR code customer yang berisi ID pesanan. Hasil scan akan menampilkan menu sesuai vendor terpilih dan status pembayaran.
                        </div>
                    </div>
                </div>

                <div class="row mt-3">
                    <div class="col-md-12">
                        <div id="reader" style="width: 100%; max-width: 780px; margin: 0 auto; border: 2px solid #ddd; border-radius: 8px;"></div>
                    </div>
                </div>

                <div class="row mt-3">
                    <div class="col-md-12">
                        <button id="btnStartScan" class="btn btn-gradient-primary me-2">Mulai Scan</button>
                        <button id="btnStopScan" class="btn btn-gradient-danger" style="display:none;">Hentikan Scan</button>
                        <button id="btnReset" class="btn btn-gradient-secondary" style="display:none;">Scan Lagi</button>
                    </div>
                </div>

                <div class="mt-3">
                    <small id="cameraStatus" class="text-muted">Menunggu izin kamera...</small>
                </div>
            </div>
        </div>

        <div id="resultContainer" class="card" style="display:none;">
            <div class="card-body">
                <h4 class="card-title">Hasil Baca QR</h4>
                <p class="mb-1"><strong>ID Pesanan:</strong> <span id="resultOrderId">-</span></p>
                <p class="mb-1"><strong>Vendor:</strong> <span id="resultVendorName">-</span></p>
                <p class="mb-1"><strong>Status Bayar:</strong> <span id="resultStatusBayar">-</span></p>
                <hr>
                <div class="table-responsive">
                    <table class="table table-bordered">
                        <thead>
                            <tr>
                                <th>Menu</th>
                                <th>Jumlah</th>
                                <th>Subtotal</th>
                            </tr>
                        </thead>
                        <tbody id="resultMenuBody"></tbody>
                    </table>
                </div>
            </div>
        </div>

        <div id="errorContainer" class="alert alert-danger mt-3" style="display:none;">
            <span id="errorMessage">QR tidak valid.</span>
        </div>
    </div>

<audio id="beepSound" preload="auto">
    <source src="{{ asset('assets/sound/dragon-studio-censor-beep-3-372460.mp3') }}" type="audio/mpeg">
</audio>

<script src="https://unpkg.com/html5-qrcode@2.3.8/html5-qrcode.min.js"></script>
<script>
document.addEventListener('DOMContentLoaded', function () {
    const vendorSelect = document.getElementById('vendorSelect');
    const btnStartScan = document.getElementById('btnStartScan');
    const btnStopScan = document.getElementById('btnStopScan');
    const btnReset = document.getElementById('btnReset');
    const cameraStatus = document.getElementById('cameraStatus');
    const resultContainer = document.getElementById('resultContainer');
    const errorContainer = document.getElementById('errorContainer');
    const errorMessage = document.getElementById('errorMessage');
    const beepSound = document.getElementById('beepSound');

    let scanner = null;
    let scanning = false;

    function showError(message) {
        errorMessage.textContent = message;
        errorContainer.style.display = 'block';
        resultContainer.style.display = 'none';
    }

    function playBeep() {
        beepSound.currentTime = 0;
        beepSound.play().catch(() => {});
    }

    function stopScanner() {
        if (scanner && scanning) {
            return scanner.stop().then(function () {
                scanning = false;
                btnStartScan.style.display = 'inline-block';
                btnStopScan.style.display = 'none';
                btnReset.style.display = 'inline-block';
                cameraStatus.textContent = 'Scanner dihentikan.';
            });
        }
        return Promise.resolve();
    }

    function renderOrder(result) {
        document.getElementById('resultOrderId').textContent = result.idpesanan;
        document.getElementById('resultVendorName').textContent = result.vendor.nama_vendor;
        document.getElementById('resultStatusBayar').textContent = result.status_text;

        const tbody = document.getElementById('resultMenuBody');
        tbody.innerHTML = '';

        if (!result.items.length) {
            tbody.innerHTML = '<tr><td colspan="3" class="text-center text-muted">Tidak ada menu untuk vendor ini.</td></tr>';
        } else {
            result.items.forEach(function (item) {
                const row = document.createElement('tr');
                row.innerHTML = `
                    <td>${item.nama_menu}</td>
                    <td>${item.jumlah}</td>
                    <td>Rp ${new Intl.NumberFormat('id-ID').format(item.subtotal)}</td>
                `;
                tbody.appendChild(row);
            });
        }

        resultContainer.style.display = 'block';
        errorContainer.style.display = 'none';
    }

    function fetchOrder(decodedText) {
        const vendorId = vendorSelect.value;
        
        // Handle both JSON payload (legacy) dan simple ID (new)
        let idpenjualan = decodedText;
        try {
            const parsed = JSON.parse(decodedText);
            if (parsed.idpenjualan) {
                idpenjualan = parsed.idpenjualan;
            }
        } catch (e) {
            // bukan JSON, gunakan decodedText as-is
        }
        
        const url = `{{ url('/vendor/order') }}/${idpenjualan}?vendor_id=${encodeURIComponent(vendorId)}`;

        fetch(url)
            .then(function (response) {
                if (!response.ok) {
                    throw new Error('Pesanan tidak ditemukan atau vendor tidak valid');
                }
                return response.json();
            })
            .then(function (data) {
                renderOrder(data);
            })
            .catch(function (error) {
                console.error(error);
                showError(error.message || 'Gagal membaca pesanan');
            });
    }

    function onScanSuccess(decodedText) {
        playBeep();
        stopScanner().then(function () {
            fetchOrder(decodedText);
        });
    }

    function onScanError() {
        // ignore noisy scan errors
    }

    btnStartScan.addEventListener('click', function () {
        if (!scanner) {
            scanner = new Html5Qrcode('reader');
        }

        scanner.start(
            { facingMode: 'environment' },
            { fps: 10, qrbox: { width: 260, height: 260 } },
            onScanSuccess,
            onScanError
        ).then(function () {
            scanning = true;
            btnStartScan.style.display = 'none';
            btnStopScan.style.display = 'inline-block';
            btnReset.style.display = 'none';
            cameraStatus.textContent = 'Scanning aktif...';
            resultContainer.style.display = 'none';
            errorContainer.style.display = 'none';
        }).catch(function (error) {
            console.error(error);
            showError('Gagal membuka kamera. Pastikan browser mengizinkan kamera dan tidak sedang dipakai aplikasi lain.');
        });
    });

    btnStopScan.addEventListener('click', function () {
        stopScanner();
    });

    btnReset.addEventListener('click', function () {
        resultContainer.style.display = 'none';
        errorContainer.style.display = 'none';
        btnReset.style.display = 'none';
        cameraStatus.textContent = 'Menunggu izin kamera...';
        btnStartScan.click();
    });
});
</script>
@endsection
