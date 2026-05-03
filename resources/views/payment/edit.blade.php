@extends('layouts.app')

@section('title', 'Edit Transaksi')

@section('content')
<div class="main-panel">
    <div class="content-wrapper">
        <div class="card">
            <div class="card-body">
                <div class="d-flex justify-content-between align-items-center mb-3">
                    <div>
                        <h4 class="card-title mb-0">Edit Transaksi #{{ $penjualan->idpenjualan }}</h4>
                        <small class="text-muted">Perbarui total, status, atau metode pembayaran.</small>
                    </div>
                    <a href="{{ route('payment.index', ['id' => $penjualan->idpenjualan]) }}" class="btn btn-light">Kembali</a>
                </div>

                <div class="row mb-3">
                    <div class="col-md-4">
                        <div class="p-3 border rounded">
                            <small class="text-muted d-block">Total Saat Ini</small>
                            <strong>Rp {{ number_format($penjualan->total, 0, ',', '.') }}</strong>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="p-3 border rounded">
                            <small class="text-muted d-block">Status Saat Ini</small>
                            <strong>{{ (int) $penjualan->status_bayar === 1 ? 'Lunas' : 'Pending' }}</strong>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="p-3 border rounded">
                            <small class="text-muted d-block">Order ID</small>
                            <strong>{{ $penjualan->order_id ?? '-' }}</strong>
                        </div>
                    </div>
                </div>

                <form action="{{ route('payment.update', $penjualan->idpenjualan) }}" method="POST" class="row g-3">
                    @csrf
                    @method('PUT')

                    <div class="col-md-4">
                        <label class="form-label">Total</label>
                        <input type="number" name="total" class="form-control @error('total') is-invalid @enderror" value="{{ old('total', $penjualan->total) }}" min="0" required>
                        @error('total')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    </div>

                    <div class="col-md-4">
                        <label class="form-label">Status Bayar</label>
                        <select name="status_bayar" class="form-select @error('status_bayar') is-invalid @enderror" required>
                            <option value="0" @selected(old('status_bayar', $penjualan->status_bayar) == 0)>Pending</option>
                            <option value="1" @selected(old('status_bayar', $penjualan->status_bayar) == 1)>Lunas</option>
                        </select>
                        @error('status_bayar')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    </div>

                    <div class="col-md-4">
                        <label class="form-label">Payment Type</label>
                        <input type="text" name="payment_type" class="form-control @error('payment_type') is-invalid @enderror" value="{{ old('payment_type', $penjualan->payment_type) }}" placeholder="qris / gopay / manual_sync">
                        @error('payment_type')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    </div>

                    <div class="col-12 d-flex gap-2">
                        <button type="submit" class="btn btn-gradient-primary">Simpan Perubahan</button>
                        <a href="{{ route('payment.index', ['id' => $penjualan->idpenjualan]) }}" class="btn btn-light">Batal</a>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
@endsection
