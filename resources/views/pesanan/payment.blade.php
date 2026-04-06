@extends('layouts.app')

@section('content')
<div class="container">
    <h1>Pembayaran Pesanan</h1>

    <div class="card">
        <div class="card-body">
            <h5>Pesanan #{{ $pesanan->idpesanan }}</h5>
            <p>Nama: {{ $pesanan->nama }}</p>
            <p>Total: Rp {{ number_format($pesanan->total) }}</p>
            <p>Metode: {{ $pesanan->metode_text }}</p>
            <p>Status: {{ $pesanan->status_text }}</p>

            <h6>Detail:</h6>
            <ul>
                @foreach($pesanan->detail as $detail)
                    <li>{{ $detail->menu->nama_menu }} x {{ $detail->jumlah }} = Rp {{ number_format($detail->subtotal) }}</li>
                @endforeach
            </ul>

            @if($pesanan->status_bayar == 0)
                <button id="pay-button" class="btn btn-primary">Bayar Sekarang</button>
            @else
                <p class="text-success">Pembayaran Lunas</p>
            @endif
        </div>
    </div>
</div>

@if($pesanan->status_bayar == 0)
<script src="https://app.sandbox.midtrans.com/snap/snap.js" data-client-key="{{ config('midtrans.client_key') }}"></script>
<script>
    $('#pay-button').click(function() {
        $.post('/payment/checkout', {
            order_id: '{{ $pesanan->idpesanan }}',
            _token: '{{ csrf_token() }}'
        }, function(response) {
            snap.pay(response.snap_token, {
                onSuccess: function(result) {
                    alert('Pembayaran berhasil!');
                    location.reload();
                },
                onPending: function(result) {
                    alert('Pembayaran pending');
                },
                onError: function(result) {
                    alert('Pembayaran gagal');
                }
            });
        });
    });
</script>
@endif
@endsection