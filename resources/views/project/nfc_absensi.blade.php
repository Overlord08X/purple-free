@extends('layouts.app')
<meta name="csrf-token" content="{{ csrf_token() }}">
@section('title', 'NFC Absensi')

@section('content')
<div class="main-panel">
    <div class="content-wrapper">
        <div class="page-header">
            <h3 class="page-title">
                <span class="page-title-icon bg-gradient-primary text-white me-2">
                    <i class="mdi mdi-nfc"></i>
                </span>
                NFC Absensi Mahasiswa
            </h3>
        </div>

        <div class="card">
            <div class="card-body">
                <h4 class="card-title mb-3">Scanner Kartu NFC</h4>

                <div class="alert alert-info" id="infoBox">
                    Tempelkan kartu NFC ke perangkat untuk melakukan absensi otomatis.
                </div>

                <div class="row g-3 mb-3">
                    <div class="col-md-4">
                        <div class="border rounded p-3 h-100">
                            <small class="text-muted d-block">Status Scanner</small>
                            <div class="fw-bold" id="scannerStatus">Siap</div>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="border rounded p-3 h-100">
                            <small class="text-muted d-block">Serial Number</small>
                            <div class="fw-bold" id="serialNumberText">-</div>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="border rounded p-3 h-100">
                            <small class="text-muted d-block">Nama Mahasiswa</small>
                            <div class="fw-bold" id="namaMahasiswaText">-</div>
                        </div>
                    </div>
                </div>

                <div class="mb-3">
                    <button class="btn btn-gradient-primary me-2" id="btnStartNfc">
                        <i class="mdi mdi-play"></i> Mulai Scan NFC
                    </button>
                    <button class="btn btn-gradient-secondary" id="btnClearResult">
                        <i class="mdi mdi-refresh"></i> Bersihkan Hasil
                    </button>
                </div>

                <div id="resultBox" class="alert" style="display:none;"></div>

                <div class="border rounded p-3 mt-3 bg-light">
                    <div class="d-flex justify-content-between align-items-center mb-2">
                        <strong>Debug NFC</strong>
                        <small class="text-muted">Status event pembacaan</small>
                    </div>
                    <div id="debugLog" class="small" style="max-height: 180px; overflow:auto; white-space: pre-wrap;"></div>
                </div>
            </div>
        </div>
    </div>

<script>
document.addEventListener('DOMContentLoaded', () => {
    const btnStartNfc = document.getElementById('btnStartNfc');
    const btnClearResult = document.getElementById('btnClearResult');
    const scannerStatus = document.getElementById('scannerStatus');
    const serialNumberText = document.getElementById('serialNumberText');
    const namaMahasiswaText = document.getElementById('namaMahasiswaText');
    const resultBox = document.getElementById('resultBox');
    const debugLog = document.getElementById('debugLog');
    const csrfToken = document.querySelector('meta[name="csrf-token"]').getAttribute('content');
    const isFirefox = /firefox/i.test(navigator.userAgent);

    let ndefReader = null;
    let scanSessionActive = false;
    let readingTimeoutId = null;
    let lastSentSerial = null;
    let lastSentAt = 0;

    const setStatus = (text) => {
        scannerStatus.textContent = text;
    };

    const logDebug = (message) => {
        const time = new Date().toLocaleTimeString();
        debugLog.innerHTML += `[${time}] ${message}<br>`;
        debugLog.scrollTop = debugLog.scrollHeight;
    };

    const showResult = (type, message) => {
        resultBox.style.display = 'block';
        resultBox.className = `alert alert-${type}`;
        resultBox.textContent = message;
    };

    const clearResult = () => {
        serialNumberText.textContent = '-';
        namaMahasiswaText.textContent = '-';
        resultBox.style.display = 'none';
        resultBox.textContent = '';
        setStatus('Siap');
        debugLog.innerHTML = '';
        logDebug('Debug log dibersihkan.');
    };

    const sendAttendance = async (serialNumber) => {
        const now = Date.now();

        // Menghindari request ganda ketika tag yang sama terbaca berulang dalam waktu sangat dekat.
        if (lastSentSerial === serialNumber && now - lastSentAt < 3000) {
            logDebug(`Serial ${serialNumber} di-skip karena duplikat dalam 3 detik.`);
            return;
        }

        lastSentSerial = serialNumber;
        lastSentAt = now;

        try {
            logDebug(`Mengirim absensi ke backend untuk serial: ${serialNumber}`);
            const response = await fetch('/api/absensi', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'Accept': 'application/json',
                    'X-CSRF-TOKEN': csrfToken,
                    'X-Requested-With': 'XMLHttpRequest'
                },
                body: JSON.stringify({ serialNumber })
            });

            const payload = await response.json();
            namaMahasiswaText.textContent = payload.namaMahasiswa || '-';

            if (!response.ok || payload.status !== 'sukses') {
                logDebug(`Backend menolak absensi: ${payload.message || 'unknown error'} (status ${response.status})`);
                showResult('danger', payload.message || 'Absensi gagal diproses.');
                return;
            }

            logDebug(`Absensi sukses untuk ${payload.namaMahasiswa || '-'} dengan serial ${serialNumber}`);
            showResult('success', payload.message || 'Absensi berhasil disimpan.');
        } catch (error) {
            console.error('Gagal mengirim absensi:', error);
            logDebug(`Gagal request backend: ${error.message}`);
            showResult('warning', 'Koneksi ke server bermasalah. Periksa jaringan Anda.');
        }
    };

    const startNfcScanner = async () => {
        logDebug('Tombol scan ditekan. Mencoba memulai Web NFC...');

        if (!('NDEFReader' in window)) {
            showResult(
                'warning',
                isFirefox
                    ? 'Firefox belum mendukung Web NFC API. Gunakan Chrome Android untuk scan NFC.'
                    : 'Browser ini tidak mendukung Web NFC API. Gunakan browser yang kompatibel untuk scan NFC.'
            );
            setStatus('Tidak didukung');
            return;
        }

        try {
            if (ndefReader && scanSessionActive) {
                logDebug('Scanner sudah aktif. Tidak membuat session baru.');
                showResult('info', 'Scanner NFC sudah aktif. Tempelkan kartu ke perangkat.');
                setStatus('Scanning aktif');
                return;
            }

            ndefReader = new NDEFReader();
            ndefReader.onreadingerror = () => {
                logDebug('Event readingerror terpanggil. Tag tidak terbaca.');
                showResult('warning', 'Tag NFC tidak dapat dibaca. Coba tempelkan ulang kartu.');
            };

            ndefReader.onreading = (event) => {
                const serialNumber = (event.serialNumber || '').trim();
                logDebug(`Event reading terpanggil. Serial terbaca: ${serialNumber || '(kosong)'}`);

                if (readingTimeoutId) {
                    clearTimeout(readingTimeoutId);
                    readingTimeoutId = null;
                }

                if (!serialNumber) {
                    showResult('warning', 'Serial number kartu tidak tersedia dari perangkat ini.');
                    return;
                }

                serialNumberText.textContent = serialNumber;
                setStatus('Tag terbaca');
                sendAttendance(serialNumber);
            };

            // scan() harus dipanggil setelah handler siap supaya event pertama tidak terlewat.
            await ndefReader.scan();
            scanSessionActive = true;
            setStatus('Scanning aktif');
            showResult('info', 'Scanner aktif. Tempelkan kartu NFC ke perangkat.');
            logDebug('Web NFC berhasil diaktifkan. Menunggu event reading...');

            if (readingTimeoutId) {
                clearTimeout(readingTimeoutId);
            }

            readingTimeoutId = window.setTimeout(() => {
                if (scanSessionActive) {
                    logDebug('15 detik tanpa event reading. Jika kartu terbaca di app lain, kemungkinan tag bukan NDEF atau tidak kompatibel dengan Web NFC.');
                    showResult('warning', 'Sudah menunggu 15 detik tanpa event reading. Jika kartu terbaca di aplikasi lain, besar kemungkinan tag bukan NDEF atau tidak kompatibel dengan Web NFC.');
                }
            }, 15000);
        } catch (error) {
            logDebug(`Gagal memulai NFC: ${error.name} - ${error.message}`);
            console.error('Gagal memulai scanner NFC:', error);

            if (error.name === 'NotAllowedError') {
                showResult('danger', 'Izin NFC ditolak. Silakan izinkan akses NFC di browser Anda.');
                setStatus('Izin ditolak');
                return;
            }

            if (error.name === 'NotSupportedError') {
                showResult('warning', 'Perangkat atau browser tidak mendukung NFC.');
                setStatus('Tidak didukung');
                return;
            }

            if (error.name === 'NotReadableError') {
                showResult('warning', 'NFC kemungkinan belum aktif di perangkat. Aktifkan NFC lalu coba lagi.');
                setStatus('NFC nonaktif');
                return;
            }

            showResult('danger', 'Terjadi kesalahan saat mengakses NFC: ' + error.message);
            setStatus('Error');
        }
    };

    btnStartNfc.addEventListener('click', startNfcScanner);
    btnClearResult.addEventListener('click', clearResult);

    if (!('NDEFReader' in window)) {
        btnStartNfc.disabled = true;
        showResult(
            'warning',
            isFirefox
                ? 'Firefox belum mendukung Web NFC API. Buka halaman ini lewat Chrome Android.'
                : 'Web NFC tidak tersedia di browser ini. Gunakan browser/perangkat yang mendukung NFC.'
        );
        setStatus('Tidak didukung');
        logDebug('Web NFC tidak tersedia di browser ini.');
    }

    window.addEventListener('beforeunload', () => {
        scanSessionActive = false;
        ndefReader = null;
        if (readingTimeoutId) {
            clearTimeout(readingTimeoutId);
            readingTimeoutId = null;
        }
    });
});
</script>
@endsection
