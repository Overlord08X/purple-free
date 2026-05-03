@extends('layouts.app')

@section('content')
<div class="container">
    <h1>Pembayaran Berhasil</h1>

    <div class="card">
        <div class="card-body text-center">
            <h5>Pesanan #{{ $pesanan->idpesanan }}</h5>
            <p>Nama: {{ $pesanan->nama }}</p>
            <p>Total: Rp {{ number_format($pesanan->total) }}</p>
            <p>Status: <span class="text-success">Berhasil</span></p>
            <p>
                <a href="{{ route('project.customer', $pesanan->idpesanan) }}" class="btn btn-secondary mb-3">
                    Buka Halaman QR Customer
                </a>
            </p>

            <h6>QR Code Pesanan:</h6>
            <img src="{{ $qrCodeDataUri }}" alt="QR Code" style="width: 200px; height: 200px;" />

            <br><br>
            <a href="{{ $qrCodeDataUri }}" download="qrcode_{{ $pesanan->idpesanan }}.png" class="btn btn-primary">Download QR Code</a>
        </div>
    </div>
</div>
@endsection