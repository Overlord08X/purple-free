@extends('layouts.app')

@section('content')
<div class="container">
    <h1>Pembayaran Pesanan</h1>

    @if ($message = Session::get('info'))
        <div class="alert alert-info alert-dismissible fade show" role="alert">
            {{ $message }}
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    @endif

    @if ($message = Session::get('error'))
        <div class="alert alert-danger alert-dismissible fade show" role="alert">
            {{ $message }}
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    @endif

    <div class="card">
        <div class="card-body">
            <h5>Pesanan #{{ $pesanan->idpesanan }}</h5>
            <p>Nama: {{ $pesanan->nama }}</p>
            <p>Total: Rp {{ number_format($pesanan->total) }}</p>
            <p>Metode: {{ $pesanan->metode_text }}</p>
            <p>Status: <span class="badge {{ $pesanan->status_bayar == 1 ? 'bg-success' : 'bg-warning' }}">{{ $pesanan->status_text }}</span></p>

            <h6>Detail:</h6>
            <ul>
                @foreach($pesanan->detail as $detail)
                    <li>{{ $detail->menu->nama_menu }} x {{ $detail->jumlah }} = Rp {{ number_format($detail->subtotal) }}</li>
                @endforeach
            </ul>

            @if($pesanan->status_bayar == 0)
                <button id="pay-button" class="btn btn-primary me-2">Bayar Sekarang</button>
                <a href="{{ route('pesanan.verify-status', $pesanan->idpesanan) }}" class="btn btn-secondary">Cek Status Pembayaran</a>
            @else
                <p class="text-success fw-bold">✓ Pembayaran Berhasil!</p>
                @if(!empty($qrCodeDataUri))
                    <div class="mt-3">
                        <h6>QR Code Pesanan:</h6>
                        <img src="{{ $qrCodeDataUri }}" alt="QR Code" style="width: 150px; height: 150px;" />
                        <br><br>
                        <a href="{{ $qrCodeDataUri }}" download="qrcode_{{ $pesanan->idpesanan }}.png" class="btn btn-primary">Download QR Code</a>
                    </div>
                @endif
            @endif
        </div>
    </div>
</div>

@if($pesanan->status_bayar == 0)
<script src="https://app.sandbox.midtrans.com/snap/snap.js" data-client-key="{{ config('midtrans.client_key') }}"></script>
<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script>
    var snapOpen = false;

    $('#pay-button').click(function() {
        if (snapOpen) {
            return;
        }

        var $button = $(this);
        $button.prop('disabled', true);

        $.post("{{ route('pesanan.checkout') }}", {
            order_id: '{{ $pesanan->idpesanan }}',
            _token: '{{ csrf_token() }}'
        }, function(response) {
            if (response.error) {
                alert('Error: ' + response.error);
                $button.prop('disabled', false);
                return;
            }
            if (!response.snap_token) {
                alert('Token pembayaran tidak tersedia. Silakan cek konfigurasi Midtrans.');
                $button.prop('disabled', false);
                return;
            }

            snapOpen = true;
            snap.pay(response.snap_token, {
                onSuccess: function(result) {
                    console.log('Payment success:', result);
                    snapOpen = false;
                    window.location.href = "{{ route('pesanan.verify-status', $pesanan->idpesanan) }}";
                },
                onPending: function(result) {
                    console.log('Payment pending:', result);
                    snapOpen = false;
                    window.location.href = "{{ route('pesanan.verify-status', $pesanan->idpesanan) }}";
                },
                onError: function(result) {
                    console.log('Payment error:', result);
                    snapOpen = false;
                    alert('Pembayaran gagal. Silakan coba lagi.');
                    $button.prop('disabled', false);
                },
                onClose: function() {
                    console.log('Payment popup closed');
                    snapOpen = false;
                    $button.prop('disabled', false);
                }
            });
        }).fail(function(xhr) {
            var message = 'Terjadi kesalahan saat memproses pembayaran.';
            try {
                var errorData = JSON.parse(xhr.responseText);
                if (errorData && errorData.error) {
                    message = errorData.error;
                }
            } catch (e) {
                console.error('Parse error response:', xhr.responseText);
            }
            alert(message);
            $button.prop('disabled', false);
        });
    });
</script>
@endif
@endsection