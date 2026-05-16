<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <title>Cetak Barang - {{ $barang->idbarang }}</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            margin: 20px;
        }
        .container {
            max-width: 800px;
            margin: 0 auto;
            border: 1px solid #ddd;
            padding: 20px;
        }
        .header {
            text-align: center;
            margin-bottom: 30px;
        }
        .header h1 {
            margin: 0;
            font-size: 24px;
            color: #333;
        }
        .content {
            margin-top: 20px;
        }
        .row {
            display: flex;
            margin-bottom: 15px;
            border-bottom: 1px solid #eee;
            padding-bottom: 10px;
        }
        .label {
            font-weight: bold;
            width: 150px;
            color: #555;
        }
        .value {
            flex: 1;
            color: #333;
        }
        .footer {
            margin-top: 40px;
            text-align: right;
            font-size: 12px;
            color: #999;
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <h1>Detail Barang</h1>
        </div>

        <div class="content">
            <div class="row">
                <div class="label">ID Barang</div>
                <div class="value">{{ $barang->idbarang }}</div>
            </div>
            <div class="row">
                <div class="label">Nama Barang</div>
                <div class="value">{{ $barang->nama_barang }}</div>
            </div>
            <div class="row">
                <div class="label">Harga Barang</div>
                <div class="value">Rp {{ number_format($barang->harga_barang, 0, ',', '.') }}</div>
            </div>
            <div class="row">
                <div class="label">Tanggal Dibuat</div>
                <div class="value">{{ $barang->created_at ? $barang->created_at->format('d-m-Y H:i:s') : '-' }}</div>
            </div>
        </div>

        <div class="footer">
            <p>Dicetak pada: {{ now()->format('d-m-Y H:i:s') }}</p>
        </div>
    </div>
</body>
</html>
