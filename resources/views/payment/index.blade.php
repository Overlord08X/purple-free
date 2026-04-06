@extends('layouts.app')

@section('title', 'Payment')

@section('content')
<div class="main-panel">
    <div class="content-wrapper">

        <div class="card">
            <div class="card-body text-center">

                <h3>Total Bayar</h3>
                <h1 class="text-success">Rp {{ number_format($penjualan->total ?? 0, 0, ',', '.') }}</h1>

                <h4>Detail Pembelian</h4>
                @if($penjualan && count($details) > 0)
                <div class="table-responsive">
                    <table class="table table-sm">
                        <thead>
                            <tr>
                                <th>Barang</th>
                                <th>Jumlah</th>
                                <th>Harga</th>
                                <th>Subtotal</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($details as $detail)
                            <tr>
                                <td>{{ $detail->nama_barang }}</td>
                                <td>{{ $detail->jumlah }}</td>
                                                <td>Rp {{ number_format($detail->harga ?? $detail->harga_barang ?? 0, 0, ',', '.') }}</td>
                                <td>Rp {{ number_format($detail->subtotal ?? 0, 0, ',', '.') }}</td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
                <button id="pay-button" class="btn btn-success mt-3">
                    Bayar Sekarang
                </button>
                @else
                <div class="alert alert-warning">
                    Tidak ada data transaksi. Silakan kembali ke POS dan pilih barang terlebih dahulu.
                </div>
                @endif

            </div>
        </div>

    </div>


    <script src="https://app.sandbox.midtrans.com/snap/snap.js"
        data-client-key="{{ env('MIDTRANS_CLIENT_KEY') }}"></script>

    <script>
        const payButton = document.getElementById('pay-button');
        const orderId = '{{ $penjualan->idpenjualan ?? 0 }}';

        if (payButton && orderId !== '0') {
            payButton.addEventListener('click', async function () {
                try {
                    const res = await fetch('/checkout', {
                        method: 'POST',
                        headers: {
                            'X-CSRF-TOKEN': '{{ csrf_token() }}',
                            'Accept': 'application/json',
                            'Content-Type': 'application/x-www-form-urlencoded'
                        },
                        body: new URLSearchParams({
                            order_id: orderId
                        })
                    });

                    if (!res.ok) {
                        const errorText = await res.text();
                        alert('Gagal mengambil token payment: ' + errorText);
                        return;
                    }

                    const data = await res.json();

                    if (!window.snap) {
                        alert('Midtrans snap.js belum siap. Coba refresh halaman.');
                        return;
                    }

                    if (!data.snap_token) {
                        alert('Token Midtrans tidak tersedia. Cek konfigurasi server.');
                        return;
                    }

                    window.snap.pay(data.snap_token, {
                        onError: function (result) {
                            alert('Pembayaran gagal. Cek konsol browser.');
                            console.error(result);
                        },
                        onClose: function () {
                            alert('Pembayaran dibatalkan.');
                        }
                    });
                } catch (error) {
                    alert('Terjadi kesalahan saat memproses pembayaran.');
                    console.error(error);
                }
            });
        }
    </script>

@endsection