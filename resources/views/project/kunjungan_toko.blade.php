@extends('layouts.app')

@section('title', 'Kunjungan Toko')

@section('content')
<div class="main-panel">
    <div class="content-wrapper">
        <div class="page-header">
            <h3 class="page-title">
                <span class="page-title-icon bg-gradient-primary text-white me-2">
                    <i class="mdi mdi-map-marker-radius"></i>
                </span>
                Kunjungan Toko
            </h3>
        </div>

        @if(session('success'))
        <div class="alert alert-success alert-dismissible fade show" role="alert">
            {{ session('success') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
        @endif

        @if($errors->any())
        <div class="alert alert-danger alert-dismissible fade show" role="alert">
            <strong>Terjadi kesalahan:</strong>
            <ul class="mb-0 mt-2">
                @foreach($errors->all() as $error)
                <li>{{ $error }}</li>
                @endforeach
            </ul>
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
        @endif

        <div class="row g-4">
            <div class="col-lg-5">
                <div class="card shadow-sm h-100">
                    <div class="card-body">
                        <h4 class="card-title mb-3">Input Titik Awal</h4>
                        <p class="text-muted mb-4">Ambil lokasi toko dengan akurasi terbaik, lalu simpan sebagai titik awal toko.</p>

                        <form method="POST" action="{{ route('project.kunjungan.toko.store') }}" id="formTitikAwal">
                            @csrf
                            <div class="mb-3">
                                <label class="form-label">Pilih Toko</label>
                                <select name="vendor_id" id="storeVendorSelect" class="form-select" required>
                                    <option value="">-- Pilih Toko --</option>
                                    @foreach($vendors as $vendor)
                                    <option value="{{ $vendor->idvendor }}"
                                        data-idvendor="{{ $vendor->idvendor }}"
                                        data-nama-vendor="{{ e($vendor->nama_vendor) }}"
                                        data-barcode="{{ e($vendor->barcode_label) }}"
                                        data-latitude="{{ $vendor->latitude !== null ? $vendor->latitude : '' }}"
                                        data-longitude="{{ $vendor->longitude !== null ? $vendor->longitude : '' }}"
                                        data-accuracy="{{ $vendor->accuracy !== null ? $vendor->accuracy : '' }}">
                                        {{ $vendor->nama_vendor }}
                                    </option>
                                    @endforeach
                                </select>
                            </div>

                            <div class="mb-3">
                                <label class="form-label">Barcode / QR Token</label>
                                <input type="text" name="barcode" id="barcodeField" class="form-control" placeholder="TOKO-1">
                            </div>

                            <div class="row g-3">
                                <div class="col-md-4">
                                    <label class="form-label">Latitude</label>
                                    <input type="text" name="latitude" id="storeLatitude" class="form-control" required>
                                </div>
                                <div class="col-md-4">
                                    <label class="form-label">Longitude</label>
                                    <input type="text" name="longitude" id="storeLongitude" class="form-control" required>
                                </div>
                                <div class="col-md-4">
                                    <label class="form-label">Accuracy (m)</label>
                                    <input type="text" name="accuracy" id="storeAccuracy" class="form-control" required>
                                </div>
                            </div>

                            <div class="d-flex gap-2 mt-4 flex-wrap">
                                <button type="button" class="btn btn-outline-primary" id="btnCaptureStoreLocation">
                                    Ambil Lokasi Saat Ini
                                </button>
                                <button type="submit" class="btn btn-gradient-primary">
                                    Simpan Titik Awal
                                </button>
                            </div>
                        </form>

                        <div class="alert alert-info mt-4 mb-0">
                            Target akurasi disarankan <strong>&le; 50 meter</strong> untuk titik awal toko.
                        </div>
                        <div id="geoStatus" class="small text-muted mt-2">Status lokasi: siap</div>
                    </div>
                </div>
            </div>

            <div class="col-lg-7">
                <div class="card shadow-sm h-100">
                    <div class="card-body">
                        <h4 class="card-title mb-3">Titik Kunjungan</h4>
                        <p class="text-muted mb-3">Scan barcode / QR toko, ambil lokasi sales, lalu verifikasi radius kunjungan.</p>

                        <div class="row g-3">
                            <div class="col-lg-6">
                                <div id="reader" style="min-height: 320px; border: 2px dashed #d8dfe8; border-radius: 14px; overflow: hidden;"></div>
                                <div class="d-flex gap-2 mt-3 flex-wrap">
                                    <button type="button" class="btn btn-gradient-primary" id="btnStartScan">
                                        Mulai Scan
                                    </button>
                                    <button type="button" class="btn btn-gradient-danger" id="btnStopScan" style="display:none;">
                                        Hentikan Scan
                                    </button>
                                </div>
                            </div>
                            <div class="col-lg-6">
                                <div class="card border">
                                    <div class="card-body">
                                        <h6 class="card-title">Data Toko Terpilih</h6>
                                        <div class="mb-2"><strong>Nama:</strong> <span id="selectedStoreName">-</span></div>
                                        <div class="mb-2"><strong>Barcode:</strong> <span id="selectedStoreBarcode">-</span></div>
                                        <div class="mb-2"><strong>Lokasi Toko:</strong> <span id="selectedStoreLocation">-</span></div>
                                        <div class="mb-0"><strong>Akurasi Toko:</strong> <span id="selectedStoreAccuracy">-</span></div>
                                    </div>
                                </div>

                                <form id="formKunjungan" class="mt-3">
                                    @csrf
                                    <input type="hidden" name="vendor_id" id="visitVendorId">
                                    <div class="row g-2">
                                        <div class="col-md-4">
                                            <label class="form-label">Lat Sales</label>
                                            <input type="text" name="sales_latitude" id="salesLatitude" class="form-control" required>
                                        </div>
                                        <div class="col-md-4">
                                            <label class="form-label">Lng Sales</label>
                                            <input type="text" name="sales_longitude" id="salesLongitude" class="form-control" required>
                                        </div>
                                        <div class="col-md-4">
                                            <label class="form-label">Akurasi</label>
                                            <input type="text" name="sales_accuracy" id="salesAccuracy" class="form-control" required>
                                        </div>
                                    </div>
                                    <div class="mt-3">
                                        <label class="form-label">Threshold Maksimal (meter)</label>
                                        <input type="number" name="max_distance" id="maxDistance" class="form-control" value="300" min="0">
                                    </div>
                                    <div class="d-flex gap-2 mt-3 flex-wrap">
                                        <button type="button" class="btn btn-outline-primary" id="btnCaptureSalesLocation">
                                            Ambil Lokasi Sales
                                        </button>
                                        <button type="submit" class="btn btn-gradient-success">
                                            Verifikasi Kunjungan
                                        </button>
                                    </div>
                                </form>

                                <div id="verificationResult" class="alert mt-4 d-none"></div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="card shadow-sm mt-4">
            <div class="card-body">
                <div class="d-flex justify-content-between align-items-center flex-wrap gap-2 mb-3">
                    <h4 class="card-title mb-0">List Toko</h4>
                    <span class="text-muted">Barcode, titik awal, dan tombol cetak barcode</span>
                </div>

                <div class="table-responsive">
                    <table class="table table-hover align-middle">
                        <thead class="table-light">
                            <tr>
                                <th>Barcode</th>
                                <th>Nama Toko</th>
                                <th>Latitude</th>
                                <th>Longitude</th>
                                <th>Accuracy</th>
                                <th class="text-end">Aksi</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($vendors as $vendor)
                            <tr>
                                <td><span class="badge bg-dark">{{ $vendor->barcode_label }}</span></td>
                                <td>
                                    <div class="fw-semibold">{{ $vendor->nama_vendor }}</div>
                                    <small class="text-muted">ID: {{ $vendor->idvendor }}</small>
                                </td>
                                <td>{{ $vendor->latitude !== null ? $vendor->latitude : '-' }}</td>
                                <td>{{ $vendor->longitude !== null ? $vendor->longitude : '-' }}</td>
                                <td>{{ $vendor->accuracy !== null ? $vendor->accuracy . ' m' : '-' }}</td>
                                <td class="text-end">
                                    <button type="button" class="btn btn-sm btn-outline-primary btn-select-store"
                                        data-idvendor="{{ $vendor->idvendor }}"
                                        data-nama-vendor="{{ e($vendor->nama_vendor) }}"
                                        data-barcode="{{ e($vendor->barcode_label) }}"
                                        data-latitude="{{ $vendor->latitude !== null ? $vendor->latitude : '' }}"
                                        data-longitude="{{ $vendor->longitude !== null ? $vendor->longitude : '' }}"
                                        data-accuracy="{{ $vendor->accuracy !== null ? $vendor->accuracy : '' }}">
                                        Pilih
                                    </button>
                                    <a href="{{ route('project.kunjungan.toko.barcode', $vendor->idvendor) }}" target="_blank" class="btn btn-sm btn-gradient-primary">
                                        Cetak Barcode
                                    </a>
                                </td>
                            </tr>
                            @empty
                            <tr>
                                <td colspan="6" class="text-center text-muted">Belum ada data toko</td>
                            </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>

    <script src="https://unpkg.com/html5-qrcode@2.3.8/html5-qrcode.min.js"></script>
    <script>
        const vendorMap = new Map();
        const barcodeMap = new Map();
        const geoStatus = document.getElementById('geoStatus');
        let scanner = null;
        let scanning = false;

        function normalizeStore(raw) {
            if (!raw) {
                return null;
            }

            const store = {
                idvendor: raw.idvendor,
                nama_vendor: raw.nama_vendor,
                barcode: raw.barcode,
                latitude: raw.latitude,
                longitude: raw.longitude,
                accuracy: raw.accuracy,
            };

            store.idvendor = String(store.idvendor || '').trim();
            store.nama_vendor = (store.nama_vendor || '').trim();
            store.barcode = (store.barcode || '').trim();
            store.latitude = store.latitude === '' || store.latitude === null || store.latitude === undefined ? null : parseFloat(store.latitude);
            store.longitude = store.longitude === '' || store.longitude === null || store.longitude === undefined ? null : parseFloat(store.longitude);
            store.accuracy = store.accuracy === '' || store.accuracy === null || store.accuracy === undefined ? null : parseFloat(store.accuracy);

            return store;
        }

        function registerStore(store) {
            if (!store || !store.idvendor) {
                return;
            }

            vendorMap.set(String(store.idvendor), store);
            if (store.barcode) {
                barcodeMap.set(String(store.barcode), store);
            }
        }

        function collectStoresFromDom() {
            document.querySelectorAll('.btn-select-store').forEach((button) => {
                registerStore(normalizeStore({
                    idvendor: button.dataset.idvendor,
                    nama_vendor: button.dataset.namaVendor,
                    barcode: button.dataset.barcode,
                    latitude: button.dataset.latitude,
                    longitude: button.dataset.longitude,
                    accuracy: button.dataset.accuracy,
                }));
            });
        }

        function setGeoStatus(message, type = 'muted') {
            if (!geoStatus) {
                return;
            }

            geoStatus.className = `small mt-2 text-${type}`;
            geoStatus.textContent = `Status lokasi: ${message}`;
        }

        function humanizeGeoError(error) {
            if (!error) {
                return 'Gagal mengambil lokasi. Coba lagi.';
            }

            const code = Number(error.code || 0);
            if (code === 1) {
                return 'Izin lokasi ditolak. Klik ikon kunci di browser lalu izinkan Location.';
            }
            if (code === 2) {
                return 'Lokasi tidak tersedia. Pastikan GPS aktif dan sinyal cukup.';
            }
            if (code === 3) {
                return 'Waktu pengambilan lokasi habis (timeout). Coba di area dengan sinyal lebih baik.';
            }

            return error.message || 'Gagal mengambil lokasi. Coba lagi.';
        }

        function haversine(lat1, lng1, lat2, lng2) {
            const R = 6371000;
            const dLat = (lat2 - lat1) * Math.PI / 180;
            const dLng = (lng2 - lng1) * Math.PI / 180;
            const a = Math.sin(dLat / 2) * Math.sin(dLat / 2) +
                Math.cos(lat1 * Math.PI / 180) * Math.cos(lat2 * Math.PI / 180) *
                Math.sin(dLng / 2) * Math.sin(dLng / 2);
            const c = 2 * Math.atan2(Math.sqrt(a), Math.sqrt(1 - a));
            return R * c;
        }

        function getAccuratePosition(targetAccuracy = 50, maxWait = 20000) {
            return new Promise((resolve, reject) => {
                let bestResult = null;
                let resolved = false;

                const watchId = navigator.geolocation.watchPosition((position) => {
                    const acc = position.coords.accuracy;

                    if (!bestResult || acc < bestResult.coords.accuracy) {
                        bestResult = position;
                    }

                    if (acc <= targetAccuracy) {
                        resolved = true;
                        navigator.geolocation.clearWatch(watchId);
                        resolve(bestResult);
                        return;
                    }
                }, (error) => {
                    if (resolved) {
                        return;
                    }
                    navigator.geolocation.clearWatch(watchId);
                    reject(error);
                }, {
                    enableHighAccuracy: true,
                    maximumAge: 0,
                    timeout: maxWait
                });

                window.setTimeout(() => {
                    if (resolved) {
                        return;
                    }

                    resolved = true;
                    navigator.geolocation.clearWatch(watchId);

                    if (bestResult) {
                        resolve(bestResult);
                    } else {
                        reject(new Error('Timeout, tidak dapat posisi'));
                    }
                }, maxWait);
            });
        }

        function setSelectedStore(store) {
            if (!store) {
                return;
            }

            document.getElementById('visitVendorId').value = store.idvendor;
            document.getElementById('selectedStoreName').textContent = store.nama_vendor || '-';
            document.getElementById('selectedStoreBarcode').textContent = store.barcode || '-';
            document.getElementById('selectedStoreLocation').textContent = (store.latitude !== null && store.longitude !== null) ?
                `${store.latitude}, ${store.longitude}` :
                '-';
            document.getElementById('selectedStoreAccuracy').textContent = store.accuracy !== null ? `${store.accuracy} m` : '-';

            const vendorSelect = document.getElementById('storeVendorSelect');
            if (vendorSelect) {
                vendorSelect.value = store.idvendor;
            }
            document.getElementById('barcodeField').value = store.barcode || `TOKO-${store.idvendor}`;
        }

        async function captureLocation(targetLatField, targetLngField, targetAccuracyField, targetAccuracy = 50) {
            if (!navigator.geolocation) {
                throw new Error('Browser tidak mendukung geolocation. Gunakan Chrome/Firefox terbaru.');
            }

            if (!window.isSecureContext) {
                throw new Error('Halaman harus HTTPS agar lokasi bisa diakses.');
            }

            if (navigator.permissions && navigator.permissions.query) {
                try {
                    const permission = await navigator.permissions.query({ name: 'geolocation' });
                    if (permission.state === 'denied') {
                        throw new Error('Izin lokasi sedang diblokir browser. Izinkan akses lokasi lalu refresh.');
                    }
                } catch (permissionError) {
                    // Ignore permission API errors and continue to geolocation call.
                }
            }

            let position;
            try {
                setGeoStatus('mencari akurasi terbaik...', 'warning');
                position = await getAccuratePosition(targetAccuracy, 20000);
            } catch (accurateError) {
                // Fallback jika watchPosition gagal: tetap coba getCurrentPosition sekali.
                position = await new Promise((resolve, reject) => {
                    navigator.geolocation.getCurrentPosition(resolve, reject, {
                        enableHighAccuracy: true,
                        maximumAge: 0,
                        timeout: 10000,
                    });
                });
            }

            document.getElementById(targetLatField).value = position.coords.latitude.toFixed(7);
            document.getElementById(targetLngField).value = position.coords.longitude.toFixed(7);
            document.getElementById(targetAccuracyField).value = position.coords.accuracy.toFixed(2);

            if (position.coords.accuracy <= targetAccuracy) {
                setGeoStatus(`berhasil (${position.coords.accuracy.toFixed(2)} m)`, 'success');
            } else {
                setGeoStatus(`lokasi terisi, tapi akurasi masih ${position.coords.accuracy.toFixed(2)} m`, 'warning');
            }
            return position;
        }

        collectStoresFromDom();

        document.querySelectorAll('.btn-select-store').forEach((button) => {
            button.addEventListener('click', () => {
                const store = normalizeStore({
                    idvendor: button.dataset.idvendor,
                    nama_vendor: button.dataset.namaVendor,
                    barcode: button.dataset.barcode,
                    latitude: button.dataset.latitude,
                    longitude: button.dataset.longitude,
                    accuracy: button.dataset.accuracy,
                });
                setSelectedStore(store);
                window.scrollTo({
                    top: 0,
                    behavior: 'smooth'
                });
            });
        });

        document.getElementById('storeVendorSelect').addEventListener('change', function() {
            const option = this.options[this.selectedIndex];
            if (!option || !option.dataset.idvendor) {
                return;
            }

            const store = normalizeStore({
                idvendor: option.dataset.idvendor,
                nama_vendor: option.dataset.namaVendor,
                barcode: option.dataset.barcode,
                latitude: option.dataset.latitude,
                longitude: option.dataset.longitude,
                accuracy: option.dataset.accuracy,
            });
            setSelectedStore(store);
        });

        document.getElementById('btnCaptureStoreLocation').addEventListener('click', async function() {
            const btn = this;
            const oldText = btn.textContent;
            btn.disabled = true;
            btn.textContent = 'Mengambil lokasi...';
            try {
                await captureLocation('storeLatitude', 'storeLongitude', 'storeAccuracy', 50);
            } catch (error) {
                setGeoStatus('gagal', 'danger');
                alert('Gagal mengambil lokasi toko: ' + humanizeGeoError(error));
            } finally {
                btn.disabled = false;
                btn.textContent = oldText;
            }
        });

        document.getElementById('btnCaptureSalesLocation').addEventListener('click', async function() {
            const btn = this;
            const oldText = btn.textContent;
            btn.disabled = true;
            btn.textContent = 'Mengambil lokasi...';
            try {
                await captureLocation('salesLatitude', 'salesLongitude', 'salesAccuracy', 50);
            } catch (error) {
                setGeoStatus('gagal', 'danger');
                alert('Gagal mengambil lokasi sales: ' + humanizeGeoError(error));
            } finally {
                btn.disabled = false;
                btn.textContent = oldText;
            }
        });

        document.getElementById('formKunjungan').addEventListener('submit', async function(event) {
            event.preventDefault();

            const vendorId = document.getElementById('visitVendorId').value;
            if (!vendorId) {
                alert('Pilih toko terlebih dahulu.');
                return;
            }

            const formData = new FormData(this);
            const response = await fetch("{{ route('project.kunjungan.toko.verify') }}", {
                method: 'POST',
                headers: {
                    'X-CSRF-TOKEN': '{{ csrf_token() }}',
                    'Accept': 'application/json'
                },
                body: formData
            });

            const resultContainer = document.getElementById('verificationResult');
            const data = await response.json();

            if (!response.ok) {
                resultContainer.className = 'alert alert-danger mt-4';
                resultContainer.textContent = data.message || 'Kunjungan tidak dapat diverifikasi.';
                resultContainer.classList.remove('d-none');
                return;
            }

            resultContainer.className = 'alert mt-4 ' + (data.status === 'accepted' ? 'alert-success' : 'alert-warning');
            resultContainer.innerHTML = `
            <div class="fw-semibold mb-2">${data.message}</div>
            <div>Jarak aktual: <strong>${data.distance} m</strong></div>
            <div>Threshold efektif: <strong>${data.effective_threshold} m</strong></div>
            <div>Toko: <strong>${data.vendor.nama_vendor}</strong></div>
            <div>Status: <strong>${data.status.toUpperCase()}</strong></div>
        `;
            resultContainer.classList.remove('d-none');
        });

        document.getElementById('formTitikAwal').addEventListener('submit', function() {
            const vendorSelect = document.getElementById('storeVendorSelect');
            const selectedOption = vendorSelect.options[vendorSelect.selectedIndex];
            if (selectedOption && selectedOption.dataset.idvendor) {
                const store = normalizeStore({
                    idvendor: selectedOption.dataset.idvendor,
                    nama_vendor: selectedOption.dataset.namaVendor,
                    barcode: selectedOption.dataset.barcode,
                    latitude: selectedOption.dataset.latitude,
                    longitude: selectedOption.dataset.longitude,
                    accuracy: selectedOption.dataset.accuracy,
                });
                if (!document.getElementById('barcodeField').value) {
                    document.getElementById('barcodeField').value = store.barcode || `TOKO-${store.idvendor}`;
                }
            }
        });

        document.getElementById('selectedStoreName').textContent = '-';
        document.getElementById('selectedStoreBarcode').textContent = '-';
        document.getElementById('selectedStoreLocation').textContent = '-';
        document.getElementById('selectedStoreAccuracy').textContent = '-';

        const startScanButton = document.getElementById('btnStartScan');
        const stopScanButton = document.getElementById('btnStopScan');

        async function startScanner() {
            if (!window.isSecureContext) {
                throw new Error('Kamera hanya bisa diakses lewat HTTPS.');
            }

            if (!navigator.mediaDevices || !navigator.mediaDevices.getUserMedia) {
                throw new Error('Browser tidak mendukung akses kamera. Coba Chrome/Firefox terbaru.');
            }

            if (typeof Html5Qrcode === 'undefined') {
                throw new Error('Library scanner belum termuat. Coba refresh halaman.');
            }

            if (!scanner) {
                scanner = new Html5Qrcode('reader');
            }

            await scanner.start({
                    facingMode: 'environment'
                }, {
                    fps: 10,
                    qrbox: {
                        width: 250,
                        height: 250
                    }
                },
                async (decodedText) => {
                    try {
                        const parsed = JSON.parse(decodedText);
                        const store = vendorMap.get(String(parsed.idvendor)) || barcodeMap.get(String(parsed.barcode));
                        if (store) {
                            setSelectedStore(store);
                            document.getElementById('btnStopScan').click();
                        }
                    } catch (error) {
                        const store = barcodeMap.get(String(decodedText)) || vendorMap.get(String(decodedText));
                        if (store) {
                            setSelectedStore(store);
                            document.getElementById('btnStopScan').click();
                        } else {
                            alert('Barcode / QR toko tidak dikenali.');
                        }
                    }
                }
            );

            scanning = true;
            startScanButton.style.display = 'none';
            stopScanButton.style.display = 'inline-block';
        }

        startScanButton.addEventListener('click', async () => {
            try {
                await startScanner();
            } catch (error) {
                alert('Gagal memulai scanner: ' + (error.message || error));
            }
        });

        stopScanButton.addEventListener('click', async () => {
            if (scanner && scanning) {
                await scanner.stop();
                scanning = false;
                startScanButton.style.display = 'inline-block';
                stopScanButton.style.display = 'none';
            }
        });
    </script>
    @endsection