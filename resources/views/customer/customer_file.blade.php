@extends('layouts.app')

@section('title', 'Customer File')

@section('content')
<div class="main-panel">
    <div class="content-wrapper">
        <div class="page-header">
            <h3 class="page-title">
                <span class="page-title-icon bg-gradient-primary text-white me-2">
                    <i class="mdi mdi-camera"></i>
                </span>
                Tambah Customer - Simpan Foto ke FILE
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
                        <h5 class="card-title">Form Customer FILE</h5>

                        <form id="formFileCustomer" method="POST" action="/customer/file">
                            @csrf

                            <div class="form-group mb-3">
                                <label for="nama">Nama Customer <span class="text-danger">*</span></label>
                                <input type="text" class="form-control" id="nama" name="nama" required placeholder="Masukkan nama customer">
                            </div>

                            <div class="form-group mb-3">
                                <label>Preview Kamera</label>
                                <div class="border rounded p-3 bg-light text-center" style="background-color: #000; position: relative; overflow: hidden; border-radius: 5px;">
                                    <video id="videoFilePreview" style="width: 100%; height: auto; display: block; transform: scaleX(-1); border-radius: 5px;"></video>
                                </div>
                            </div>

                            <div class="form-group mb-3">
                                <div class="btn-group w-100" role="group">
                                    <button type="button" class="btn btn-gradient-primary" id="btnStartCameraFILE">
                                        <i class="mdi mdi-camera"></i> Buka Kamera
                                    </button>
                                    <button type="button" class="btn btn-gradient-warning" id="btnCaptureFilePhoto" disabled style="display: none;">
                                        <i class="mdi mdi-camera-iris"></i> Ambil Foto
                                    </button>
                                    <button type="button" class="btn btn-gradient-danger" id="btnStopCameraFILE" disabled style="display: none;">
                                        <i class="mdi mdi-stop-circle"></i> Tutup Kamera
                                    </button>
                                </div>
                            </div>

                            <div class="form-group mb-3">
                                <label>Preview Foto Hasil Capture</label>
                                <div class="border rounded p-3 bg-light text-center" style="min-height: 300px; display: flex; align-items: center; justify-content: center;">
                                    <canvas id="canvasFileCapture" style="max-width: 100%; max-height: 300px; display: none; border-radius: 5px;"></canvas>
                                    <div id="noFilePhotoText" class="text-muted">Foto akan ditampilkan di sini</div>
                                </div>
                            </div>

                            <input type="hidden" id="fotoFileBase64" name="foto">

                            <div class="form-group">
                                <button type="submit" class="btn btn-gradient-success w-100" id="btnSaveFileCustomer" disabled>
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
    let videoFileElement = document.getElementById('videoFilePreview');
    let canvasFileElement = document.getElementById('canvasFileCapture');
    let contextFileCanvas = canvasFileElement.getContext('2d');
    let streamFile = null;

    document.getElementById('btnStartCameraFILE').addEventListener('click', async function() {
        try {
            streamFile = await navigator.mediaDevices.getUserMedia({
                video: { facingMode: 'user' },
                audio: false
            });

            videoFileElement.srcObject = streamFile;
            videoFileElement.play();

            document.getElementById('btnStartCameraFILE').style.display = 'none';
            document.getElementById('btnCaptureFilePhoto').style.display = 'inline-block';
            document.getElementById('btnCaptureFilePhoto').disabled = false;
            document.getElementById('btnStopCameraFILE').style.display = 'inline-block';
            document.getElementById('btnStopCameraFILE').disabled = false;
        } catch (error) {
            alert('Error mengakses kamera: ' + error.message);
        }
    });

    document.getElementById('btnCaptureFilePhoto').addEventListener('click', function() {
        canvasFileElement.width = videoFileElement.videoWidth;
        canvasFileElement.height = videoFileElement.videoHeight;
        contextFileCanvas.drawImage(videoFileElement, 0, 0, canvasFileElement.width, canvasFileElement.height);

        let imageDataFileUrl = canvasFileElement.toDataURL('image/png');
        document.getElementById('fotoFileBase64').value = imageDataFileUrl;

        canvasFileElement.style.display = 'block';
        document.getElementById('noFilePhotoText').style.display = 'none';
        document.getElementById('btnSaveFileCustomer').disabled = false;

        alert('Foto berhasil diambil! Sekarang tinggal masukkan nama dan klik Simpan.');
    });

    document.getElementById('btnStopCameraFILE').addEventListener('click', function() {
        if (streamFile) {
            streamFile.getTracks().forEach(track => track.stop());
        }

        videoFileElement.srcObject = null;

        document.getElementById('btnStartCameraFILE').style.display = 'inline-block';
        document.getElementById('btnCaptureFilePhoto').style.display = 'none';
        document.getElementById('btnCaptureFilePhoto').disabled = true;
        document.getElementById('btnStopCameraFILE').style.display = 'none';
        document.getElementById('btnStopCameraFILE').disabled = true;
    });

    document.getElementById('formFileCustomer').addEventListener('submit', function(e) {
        let nama = document.getElementById('nama').value.trim();
        let fotoBase64 = document.getElementById('fotoFileBase64').value;

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
