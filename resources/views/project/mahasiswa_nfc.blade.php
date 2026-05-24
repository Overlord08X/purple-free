@extends('layouts.app')

@section('title', 'Registrasi Kartu NFC')

@section('content')
<div class="main-panel">
    <div class="content-wrapper">
        <div class="page-header">
            <h3 class="page-title">
                <span class="page-title-icon bg-gradient-primary text-white me-2">
                    <i class="mdi mdi-account-card-details"></i>
                </span>
                Registrasi Kartu NFC Mahasiswa
            </h3>
        </div>

        <div class="row">
            <div class="col-12 grid-margin">
                <div class="card">
                    <div class="card-body">
                        @if (session('success'))
                            <div class="alert alert-success">{{ session('success') }}</div>
                        @endif

                        @if (session('error'))
                            <div class="alert alert-danger">{{ session('error') }}</div>
                        @endif

                        @if ($errors->any())
                            <div class="alert alert-danger">
                                <ul class="mb-0">
                                    @foreach ($errors->all() as $error)
                                        <li>{{ $error }}</li>
                                    @endforeach
                                </ul>
                            </div>
                        @endif

                        <h4 class="card-title">Tambah Mahasiswa</h4>
                        <form action="{{ route('project.nfc.mahasiswa.store') }}" method="POST" class="mb-4">
                            @csrf
                            <div class="row g-3">
                                <div class="col-md-4">
                                    <label class="form-label">Nama Mahasiswa</label>
                                    <input type="text" name="nama" class="form-control" value="{{ old('nama') }}" required>
                                </div>
                                <div class="col-md-3">
                                    <label class="form-label">NIM</label>
                                    <input type="text" name="nim" class="form-control" value="{{ old('nim') }}" required>
                                </div>
                                <div class="col-md-3">
                                    <label class="form-label">Serial Number NFC</label>
                                    <input type="text" id="serialNumberInput" name="serial_number_nfc" class="form-control" value="{{ old('serial_number_nfc') }}" placeholder="04:AA:BB:CC:DD:EE" required>
                                    <small class="text-muted">Bisa diisi otomatis dari kartu NFC.</small>
                                </div>
                                <div class="col-md-2 d-flex align-items-end">
                                    <button type="button" id="btnScanNfc" class="btn btn-gradient-info w-100">Scan NFC</button>
                                </div>
                                <div class="col-md-12 col-lg-2 d-flex align-items-end">
                                    <button type="submit" class="btn btn-gradient-primary w-100">Simpan</button>
                                </div>
                            </div>
                            <div id="nfcScanStatus" class="alert alert-secondary mt-3 mb-0">
                                Status NFC: Belum memulai scan.
                            </div>

                            <div class="border rounded p-3 mt-3 bg-light">
                                <div class="d-flex justify-content-between align-items-center mb-2">
                                    <strong>Debug NFC</strong>
                                    <small class="text-muted">Status event pembacaan</small>
                                </div>
                                <div id="nfcDebugLog" class="small" style="max-height: 180px; overflow:auto; white-space: pre-wrap;"></div>
                            </div>
                        </form>

                        <h4 class="card-title">Daftar Mahasiswa Terdaftar</h4>
                        <div class="table-responsive">
                            <table class="table table-hover">
                                <thead>
                                    <tr>
                                        <th>Nama</th>
                                        <th>NIM</th>
                                        <th>Serial NFC</th>
                                        <th style="width: 240px;">Aksi</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @forelse ($mahasiswas as $mahasiswa)
                                        <tr>
                                            <td>{{ $mahasiswa->nama }}</td>
                                            <td>{{ $mahasiswa->nim }}</td>
                                            <td>{{ $mahasiswa->serial_number_nfc }}</td>
                                            <td>
                                                <button
                                                    type="button"
                                                    class="btn btn-sm btn-gradient-warning me-1"
                                                    data-bs-toggle="modal"
                                                    data-bs-target="#editMahasiswaModal{{ $mahasiswa->id }}"
                                                >
                                                    Edit
                                                </button>

                                                <form
                                                    action="{{ route('project.nfc.mahasiswa.destroy', $mahasiswa) }}"
                                                    method="POST"
                                                    class="d-inline"
                                                    onsubmit="return confirm('Hapus data mahasiswa ini?')"
                                                >
                                                    @csrf
                                                    @method('DELETE')
                                                    <button type="submit" class="btn btn-sm btn-gradient-danger">Hapus</button>
                                                </form>
                                            </td>
                                        </tr>

                                        <div class="modal fade" id="editMahasiswaModal{{ $mahasiswa->id }}" tabindex="-1" aria-hidden="true">
                                            <div class="modal-dialog">
                                                <div class="modal-content">
                                                    <form action="{{ route('project.nfc.mahasiswa.update', $mahasiswa) }}" method="POST">
                                                        @csrf
                                                        @method('PUT')
                                                        <div class="modal-header">
                                                            <h5 class="modal-title">Edit Mahasiswa</h5>
                                                            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                                                        </div>
                                                        <div class="modal-body">
                                                            <div class="mb-3">
                                                                <label class="form-label">Nama Mahasiswa</label>
                                                                <input type="text" name="nama" class="form-control" value="{{ $mahasiswa->nama }}" required>
                                                            </div>
                                                            <div class="mb-3">
                                                                <label class="form-label">NIM</label>
                                                                <input type="text" name="nim" class="form-control" value="{{ $mahasiswa->nim }}" required>
                                                            </div>
                                                            <div class="mb-3">
                                                                <label class="form-label">Serial Number NFC</label>
                                                                <input type="text" name="serial_number_nfc" class="form-control" value="{{ $mahasiswa->serial_number_nfc }}" required>
                                                            </div>
                                                        </div>
                                                        <div class="modal-footer">
                                                            <button type="button" class="btn btn-light" data-bs-dismiss="modal">Batal</button>
                                                            <button type="submit" class="btn btn-gradient-primary">Simpan Perubahan</button>
                                                        </div>
                                                    </form>
                                                </div>
                                            </div>
                                        </div>
                                    @empty
                                        <tr>
                                            <td colspan="4" class="text-center text-muted">Belum ada mahasiswa terdaftar.</td>
                                        </tr>
                                    @endforelse
                                </tbody>
                            </table>
                        </div>

                        <div class="mt-3">
                            {{ $mahasiswas->links() }}
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script>
    document.addEventListener('DOMContentLoaded', () => {
        const serialInput = document.getElementById('serialNumberInput');
        const btnScanNfc = document.getElementById('btnScanNfc');
        const nfcScanStatus = document.getElementById('nfcScanStatus');
        const nfcDebugLog = document.getElementById('nfcDebugLog');
        const isFirefox = /firefox/i.test(navigator.userAgent);

        let ndefReader = null;
        let scanSessionActive = false;
        let readingTimeoutId = null;

        const setStatus = (type, message) => {
            nfcScanStatus.className = `alert alert-${type} mt-3 mb-0`;
            nfcScanStatus.textContent = message;
        };

        const logDebug = (message) => {
            const time = new Date().toLocaleTimeString();
            nfcDebugLog.innerHTML += `[${time}] ${message}<br>`;
            nfcDebugLog.scrollTop = nfcDebugLog.scrollHeight;
        };

        const startScan = async () => {
            logDebug('Tombol scan ditekan. Mencoba memulai Web NFC...');

            if (!('NDEFReader' in window)) {
                setStatus(
                    'warning',
                    isFirefox
                        ? 'Status NFC: Firefox belum mendukung Web NFC. Gunakan Chrome Android untuk scan, atau isi serial NFC secara manual.'
                        : 'Status NFC: Browser tidak mendukung Web NFC. Gunakan Chrome Android untuk scan, atau isi serial NFC secara manual.'
                );
                return;
            }

            try {
                if (ndefReader && scanSessionActive) {
                    logDebug('Scanner sudah aktif. Tidak membuat session baru.');
                    setStatus('info', 'Status NFC: Scanner sudah aktif, tempelkan kartu NFC ke perangkat.');
                    return;
                }

                ndefReader = new NDEFReader();

                ndefReader.onreadingerror = () => {
                    logDebug('Event readingerror terpanggil. Tag tidak terbaca.');
                    setStatus('warning', 'Status NFC: Tag tidak dapat dibaca, coba tempelkan ulang.');
                };

                ndefReader.onreading = (event) => {
                    const serial = (event.serialNumber || '').trim().toUpperCase();
                    logDebug(`Event reading terpanggil. Serial terbaca: ${serial || '(kosong)'}`);

                    if (readingTimeoutId) {
                        clearTimeout(readingTimeoutId);
                        readingTimeoutId = null;
                    }

                    if (!serial) {
                        setStatus('warning', 'Status NFC: Serial number tidak tersedia pada perangkat ini.');
                        return;
                    }

                    serialInput.value = serial;
                    setStatus('success', 'Status NFC: Serial kartu berhasil diisi otomatis.');
                };

                // scan() harus dipanggil setelah handler siap supaya event pertama tidak terlewat.
                await ndefReader.scan();
                scanSessionActive = true;
                setStatus('info', 'Status NFC: Scanner aktif, tempelkan kartu NFC ke perangkat.');
                logDebug('Web NFC berhasil diaktifkan. Menunggu event reading...');

                if (readingTimeoutId) {
                    clearTimeout(readingTimeoutId);
                }

                readingTimeoutId = window.setTimeout(() => {
                    if (scanSessionActive) {
                        logDebug('15 detik tanpa event reading. Jika kartu terbaca di app lain, kemungkinan tag bukan NDEF atau tidak kompatibel dengan Web NFC.');
                        setStatus('warning', 'Status NFC: 15 detik tanpa event reading. Jika kartu terbaca di aplikasi lain, kemungkinan tag bukan NDEF atau tidak kompatibel dengan Web NFC.');
                    }
                }, 15000);
            } catch (error) {
                scanSessionActive = false;
                ndefReader = null;
                if (readingTimeoutId) {
                    clearTimeout(readingTimeoutId);
                    readingTimeoutId = null;
                }
                logDebug(`Gagal memulai NFC: ${error.name} - ${error.message}`);
                if (error.name === 'NotAllowedError') {
                    setStatus('danger', 'Status NFC: Izin NFC ditolak. Izinkan akses NFC lalu coba lagi.');
                    return;
                }

                if (error.name === 'NotSupportedError') {
                    setStatus('warning', 'Status NFC: Perangkat tidak mendukung NFC.');
                    return;
                }

                if (error.name === 'NotReadableError') {
                    setStatus('warning', 'Status NFC: NFC belum aktif. Aktifkan NFC di perangkat lalu coba lagi.');
                    return;
                }

                setStatus('danger', `Status NFC: Gagal memulai scan (${error.message}).`);
            }
        };

        btnScanNfc.addEventListener('click', startScan);

        if (!('NDEFReader' in window)) {
            btnScanNfc.disabled = true;
            setStatus(
                'warning',
                isFirefox
                    ? 'Status NFC: Firefox belum mendukung Web NFC. Silakan isi serial NFC secara manual atau gunakan Chrome Android.'
                    : 'Status NFC: Web NFC tidak tersedia di browser ini. Silakan isi serial NFC secara manual.'
            );
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
