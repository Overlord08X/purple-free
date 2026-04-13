<h3>Pembayaran Berhasil</h3>

<h4>ID Pesanan: {{ $pesanan->idpesanan }}</h4>

<h4>Total: Rp {{ number_format($pesanan->total, 0, ',', '.') }}</h4>

<hr>

<h3>QR Code Pesanan</h3>

<img src="{{ $qrCodeDataUri }}" width="250">

<p>Scan QR ini untuk verifikasi pesanan</p>