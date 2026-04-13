<!DOCTYPE html>
<html>
<head>
    <style>
        @page {
            size: A4 portrait;
            margin: 20mm;
        }

        body {
            margin: 0;
            padding: 0;
            font-family: Arial, sans-serif;
            text-align: center;
        }

        .container {
            width: 100%;
            max-width: 200mm;
        }

        .barcode {
            width: 150px;
            height: 50px;
            margin-bottom: 10px;
        }

        .id {
            font-size: 14px;
            font-weight: bold;
            margin-bottom: 5px;
        }

        .nama {
            font-size: 16px;
            font-weight: bold;
            margin-bottom: 5px;
        }

        .harga {
            font-size: 18px;
            font-weight: bold;
            color: #d9534f;
        }
    </style>
</head>
<body>
    <div class="container">
        <img src="data:image/png;base64,{{ $barcode }}" class="barcode" />
        <div class="id">ID: {{ $barang->idbarang }}</div>
        <div class="nama">{{ $barang->nama_barang }}</div>
        <div class="harga">Rp {{ number_format($barang->harga_barang, 0, ',', '.') }}</div>
    </div>
</body>
</html>