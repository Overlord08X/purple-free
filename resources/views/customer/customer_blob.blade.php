@extends('layouts.app')

@section('title', 'Customer Blob')

@section('content')
<div class="main-panel">
    <div class="content-wrapper">
        <div class="page-header">
            <h3 class="page-title">
                <span class="page-title-icon bg-gradient-primary text-white me-2">
                    <i class="mdi mdi-camera"></i>
                </span>
                Tambah Customer - Simpan Foto ke BLOB
            </h3>
        </div>

        @if ($message = Session::get('success'))
            <div class="alert alert-success alert-dismissible fade show" role="alert">
                <strong>Berhasil!</strong> {{ $message }}
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
        @endif

        @if ($message = Session::get('error'))
            <div class="alert alert-danger alert-dismissible fade show" role="alert">
                <strong>Error!</strong> {{ $message }}
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
        @endif

        <div class="row">
            <div class="col-md-8 mx-auto">
                <div class="card">
                    <div class="card-body">
                        <h5 class="card-title">Form Customer BLOB</h5>

                        <form id="formBlobCustomer" method="POST" action="/customer/blob">
                            @csrf

                            <div class="form-group mb-3">
                                <label for="nama">Nama Customer <span class="text-danger">*</span></label>
                                <input type="text" class="form-control" id="nama" name="nama" required placeholder="Masukkan nama customer">
                            </div>

                            <div class="form-group mb-3">
                                <label>Preview Kamera</label>
                                <div class="border rounded p-3 bg-light text-center" style="background-color: #000; position: relative; overflow: hidden; border-radius: 5px;">
                                    <video id="videoBlobPreview" style="width: 100%; height: auto; display: block; transform: scaleX(-1); border-radius: 5px;"></video>
                                </div>
                            </div>

                            <div class="form-group mb-3">
                                <div class="btn-group w-100" role="group">
                                    <button type="button" class="btn btn-gradient-primary" id="btnStartCameraBLOB">
                                        <i class="mdi mdi-camera"></i> Buka Kamera
                                    </button>
                                    <button type="button" class="btn btn-gradient-warning" id="btnCaptureBlobPhoto" disabled style="display: none;">
                                        <i class="mdi mdi-camera-iris"></i> Ambil Foto
                                    </button>
                                    <button type="button" class="btn btn-gradient-danger" id="btnStopCameraBLOB" disabled style="display: none;">
                                        <i class="mdi mdi-stop-circle"></i> Tutup Kamera
                                    </button>
                                </div>
                            </div>

                            <div class="form-group mb-3">
                                <label>Preview Foto Hasil Capture</label>
                                <div class="border rounded p-3 bg-light text-center" style="min-height: 300px; display: flex; align-items: center; justify-content: center;">
                                    <canvas id="canvasBlobCapture" style="max-width: 100%; max-height: 300px; display: none; border-radius: 5px;"></canvas>
                                    <div id="noBlobPhotoText" class="text-muted">Foto akan ditampilkan di sini</div>
                                </div>
                            </div>

                            <input type="hidden" id="fotoBlobBase64" name="foto">

                            <div class="form-group">
                                <button type="submit" class="btn btn-gradient-success w-100" id="btnSaveBlobCustomer" disabled>
                                    <i class="mdi mdi-content-save"></i> Simpan Customer
                                </button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>

    </div>


<script>
    let videoBlobElement = document.getElementById('videoBlobPreview');
    let canvasBlobElement = document.getElementById('canvasBlobCapture');
    let contextBlobCanvas = canvasBlobElement.getContext('2d');
    let streamBlob = null;

    document.getElementById('btnStartCameraBLOB').addEventListener('click', async function() {
        try {
            streamBlob = await navigator.mediaDevices.getUserMedia({
                video: { facingMode: 'user' },
                audio: false
            });

            videoBlobElement.srcObject = streamBlob;
            videoBlobElement.play();

            document.getElementById('btnStartCameraBLOB').style.display = 'none';
            document.getElementById('btnCaptureBlobPhoto').style.display = 'inline-block';
            document.getElementById('btnCaptureBlobPhoto').disabled = false;
            document.getElementById('btnStopCameraBLOB').style.display = 'inline-block';
            document.getElementById('btnStopCameraBLOB').disabled = false;
        } catch (error) {
            alert('Error mengakses kamera: ' + error.message);
        }
    });

    document.getElementById('btnCaptureBlobPhoto').addEventListener('click', function() {
        canvasBlobElement.width = videoBlobElement.videoWidth;
        canvasBlobElement.height = videoBlobElement.videoHeight;
        contextBlobCanvas.drawImage(videoBlobElement, 0, 0, canvasBlobElement.width, canvasBlobElement.height);

        let imageDataBlobUrl = canvasBlobElement.toDataURL('image/png');
        document.getElementById('fotoBlobBase64').value = imageDataBlobUrl;

        canvasBlobElement.style.display = 'block';
        document.getElementById('noBlobPhotoText').style.display = 'none';
        document.getElementById('btnSaveBlobCustomer').disabled = false;

        alert('Foto berhasil diambil! Sekarang tinggal masukkan nama dan klik Simpan.');
    });

    document.getElementById('btnStopCameraBLOB').addEventListener('click', function() {
        if (streamBlob) {
            streamBlob.getTracks().forEach(track => track.stop());
        }

        videoBlobElement.srcObject = null;

        document.getElementById('btnStartCameraBLOB').style.display = 'inline-block';
        document.getElementById('btnCaptureBlobPhoto').style.display = 'none';
        document.getElementById('btnCaptureBlobPhoto').disabled = true;
        document.getElementById('btnStopCameraBLOB').style.display = 'none';
        document.getElementById('btnStopCameraBLOB').disabled = true;
    });

    document.getElementById('formBlobCustomer').addEventListener('submit', function(e) {
        let nama = document.getElementById('nama').value.trim();
        let fotoBase64 = document.getElementById('fotoBlobBase64').value;

        if (!nama) {
            e.preventDefault();
            alert('Nama customer wajib diisi!');
            return false;
        }

        if (!fotoBase64) {
            e.preventDefault();
            alert('Silakan ambil foto terlebih dahulu!');
            return false;
        }

        return true;
    });
</script>

@endsection
