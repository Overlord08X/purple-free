<!DOCTYPE html>
<html>
<head>
    <title>Tambah Customer</title>
</head>
<body>

<h2>Tambah Customer + QR Code</h2>

<form action="{{ route('qrcode.store') }}" method="POST">
    @csrf

    <input type="text" name="nama" placeholder="Nama Customer">
    <br><br>

    <button type="submit">Simpan & Generate QR</button>
</form>

</body>
</html>