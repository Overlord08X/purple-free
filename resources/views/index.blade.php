@extends('layouts.app')

@section('title', 'Payment')

@section('content')
<div class="main-panel">
    <div class="content-wrapper">

        <div class="card">
            <div class="card-body text-center">

                @if(request()->query('paid') == '1')
                <div class="alert alert-success">
                    Pembayaran berhasil diproses. Silakan tunggu status pembayaran ter-update.
                </div>
                @endif

                @if(request()->query('paid') == '1' && !empty($qrCodeDataUri))
                <div class="mt-3 mb-3">
                    <h5>QR Code Transaksi</h5>
                    <p class="text-muted">Simpan QR ini sebagai bukti transaksi.</p>
                    <img src="{{ $qrCodeDataUri }}" alt="QR Code Transaksi" style="max-width:260px; width:100%; height:auto;">
                    <div class="mt-2">
                        <small class="text-muted">ID Transaksi: {{ $penjualan->idpenjualan ?? '-' }}</small>
                    </div>
                </div>
                @endif

                <h3>Total Bayar</h3>
                <h1 class="text-success">Rp {{ number_format($penjualan->total ?? 0, 0, ',', '.') }}</h1>

                <h4>Detail Pembelian</h4>
                @if($penjualan && count($details) > 0)
                <div class="table-responsive">
                    <table class="table table-sm">
                        <thead>
                            <tr>
                                <th>Item</th>
                                <th>Jumlah</th>
                                <th>Harga</th>
                                <th>Subtotal</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($details as $detail)
                            <tr>
                                <td>
                                    {{ $detail->nama_item ?? $detail->nama_barang ?? $detail->nama_menu }}
                                    @if(!empty($detail->vendor_name))
                                        <br><small class="text-muted">{{ $detail->vendor_name }}</small>
                                    @endif
                                </td>
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
                    Tidak ada data transaksi. Silakan kembali ke POS dan pilih barang atau menu terlebih dahulu.
                </div>
                @endif

            </div>
        </div>

    </div>


    <script src="https://app.sandbox.midtrans.com/snap/snap.js"
        data-client-key="{{ config('midtrans.client_key') }}"></script>

    <script>
        const payButton = document.getElementById('pay-button');
        const orderId = '{{ $penjualan->idpenjualan ?? 0 }}';
        let snapOpen = false;

        if (payButton && orderId !== '0') {
            payButton.addEventListener('click', async function() {
                if (snapOpen) {
                    return;
                }

                payButton.disabled = true;

                try {
                    const res = await fetch("{{ route('payment.checkout') }}", {
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

                    const text = await res.text();
                    let data = null;

                    try {
                        data = JSON.parse(text);
                    } catch (parseError) {
                        console.error('JSON parse error:', parseError, 'response:', text);
                    }

                    if (!res.ok) {
                        const message = data?.error || text || 'Unknown error';
                        console.error('Checkout error response:', message);
                        alert('Gagal mengambil token payment: ' + message);
                        payButton.disabled = false;
                        return;
                    }

                    if (!data || !data.snap_token) {
                        const message = data?.error || 'Token Midtrans tidak tersedia. Cek konfigurasi server.';
                        alert(message);
                        payButton.disabled = false;
                        return;
                    }

                    if (!window.snap) {
                        alert('Midtrans snap.js belum siap. Coba refresh halaman.');
                        payButton.disabled = false;
                        return;
                    }

                    snapOpen = true;
                    window.snap.pay(data.snap_token, {

                        onSuccess: function(result) {
                            console.log('SUCCESS:', result);
                            window.location.href = "/payment/" + orderId + "?paid=1";
                        },

                        onPending: function(result) {
                            console.log('PENDING:', result);
                            alert('Menunggu pembayaran...');
                        },

                        onError: function(result) {
                            console.error('Midtrans onError:', result);
                            const msg = result?.status_message || 'Pembayaran gagal. Silakan coba lagi.';
                            alert(msg);
                            snapOpen = false;
                            payButton.disabled = false;
                        },

                        onClose: function() {
                            console.log('Midtrans popup closed');
                            snapOpen = false;
                            payButton.disabled = false;
                        }
                    });
                } catch (error) {
                    alert('Terjadi kesalahan saat memproses pembayaran: ' + (error.message || error));
                    console.error(error);
                    snapOpen = false;
                    payButton.disabled = false;
                }
            });
        }
    </script>

    @endsection