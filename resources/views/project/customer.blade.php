@extends('layouts.app')

@section('title', 'QR Customer')

@section('content')
<div class="main-panel">
    <div class="content-wrapper">
        <div class="page-header">
            <h3 class="page-title">
                <span class="page-title-icon bg-gradient-primary text-white me-2">
                    <i class="mdi mdi-qrcode-scan"></i>
                </span>
                QR Customer
            </h3>
        </div>

        @if(session('success'))
            <div class="alert alert-success">{{ session('success') }}</div>
        @endif

        @if(session('error'))
            <div class="alert alert-danger">{{ session('error') }}</div>
        @endif

        <div class="card mb-3">
            <div class="card-body">
                <form method="GET" action="{{ route('project.customer') }}" class="row g-2 align-items-end">
                    <div class="col-md-8">
                        <label class="form-label">Masukkan ID Pesanan</label>
                        <input type="text" name="idpesanan" class="form-control" placeholder="Contoh: 1" value="{{ $orderId ?? '' }}">
                    </div>
                    <div class="col-md-4">
                        <button type="submit" class="btn btn-gradient-primary w-100">Lihat QR</button>
                    </div>
                </form>
            </div>
        </div>

        @if($penjualan)
            <div class="card">
                <div class="card-body text-center">
                    <h4 class="card-title mb-3">Transaksi #{{ $penjualan->idpenjualan }}</h4>
                    <p class="mb-1"><strong>Total:</strong> Rp {{ number_format($penjualan->total, 0, ',', '.') }}</p>
                    <p class="mb-3">
                        <strong>Status Bayar:</strong>
                        <span class="badge {{ $penjualan->status_bayar == 1 ? 'bg-success' : 'bg-warning' }}">
                            {{ $penjualan->status_bayar == 1 ? 'Lunas' : 'Pending' }}
                        </span>
                    </p>

                    @if($qrCodeDataUri)
                        <div class="mb-3">
                            <img src="{{ $qrCodeDataUri }}" alt="QR Transaksi" style="max-width: 320px; width: 100%; height: auto;">
                        </div>
                        <p class="text-muted mb-3">QR ini bisa dibuka lagi lewat link halaman ini, walaupun halaman pembayaran sudah ditutup.</p>
                        <div class="mb-3">
                            <label class="form-label">Link akses QR</label>
                            <input type="text" class="form-control text-center" value="{{ url()->current() }}" readonly onclick="this.select()">
                        </div>
                    @endif

                    <div class="table-responsive text-start mt-4">
                        <table class="table table-bordered">
                            <thead>
                                <tr>
                                    <th>Item</th>
                                    <th>Jumlah</th>
                                    <th>Subtotal</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($details as $detail)
                                    <tr>
                                        <td>{{ $detail->nama_item ?? '-' }}</td>
                                        <td>{{ $detail->jumlah }}</td>
                                        <td>Rp {{ number_format($detail->subtotal, 0, ',', '.') }}</td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="3" class="text-center text-muted">Tidak ada detail transaksi</td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        @elseif(!empty($orderId))
            <div class="alert alert-warning">
                Transaksi dengan ID <strong>{{ $orderId }}</strong> tidak ditemukan.
            </div>
        @else
            <div class="alert alert-info">
                Masukkan ID transaksi/pesanan untuk menampilkan QR code customer.
            </div>
        @endif
    </div>

@endsection
