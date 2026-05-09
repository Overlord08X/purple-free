<h3>Pembayaran Berhasil</h3>

<h4>ID Pesanan: {{ $pesanan->idpesanan }}</h4>

<h4>Total: Rp {{ number_format($pesanan->total, 0, ',', '.') }}</h4>

<hr>

<h3>QR Code Pesanan</h3>

<img src="{{ $qrCodeDataUri }}" width="250">

<p>Scan QR ini untuk verifikasi pesanan</p>

@if(!empty($pesanan))
    <p class="mt-3">
        <a href="{{ route('project.customer', $pesanan->idpesanan) }}" class="btn btn-secondary">Buka Halaman QR Customer</a>
    </p>
@endif