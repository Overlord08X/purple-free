<!doctype html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Cetak Barcode Toko</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            background: #f7f9fc;
            margin: 0;
            padding: 32px;
            color: #1f2937;
        }
        .sheet {
            max-width: 520px;
            margin: 0 auto;
            background: #fff;
            border-radius: 18px;
            padding: 28px;
            box-shadow: 0 20px 40px rgba(15, 23, 42, 0.08);
            text-align: center;
        }
        .badge {
            display: inline-block;
            padding: 6px 12px;
            border-radius: 999px;
            background: #e8f0ff;
            color: #1d4ed8;
            font-weight: 700;
            margin-bottom: 14px;
        }
        .meta {
            margin-top: 18px;
            text-align: left;
            background: #f8fafc;
            border-radius: 12px;
            padding: 14px 16px;
            line-height: 1.7;
        }
        .actions {
            margin-top: 22px;
            display: flex;
            gap: 10px;
            justify-content: center;
            flex-wrap: wrap;
        }
        .btn {
            border: 0;
            border-radius: 10px;
            padding: 10px 16px;
            font-weight: 700;
            cursor: pointer;
        }
        .btn-primary {
            background: #2563eb;
            color: #fff;
        }
        .btn-secondary {
            background: #e5e7eb;
            color: #111827;
        }
        @media print {
            body {
                background: #fff;
                padding: 0;
            }
            .actions {
                display: none;
            }
            .sheet {
                box-shadow: none;
                max-width: none;
                border-radius: 0;
                padding: 0;
            }
        }
    </style>
</head>
<body>
    <div class="sheet">
        <div class="badge">Barcode / QR Toko</div>
        <h1 style="margin: 0 0 8px; font-size: 24px;">{{ $vendor->nama_vendor }}</h1>
        <p style="margin: 0 0 18px; color: #6b7280;">{{ $payload['barcode'] }}</p>

        <img src="{{ $qrCodeDataUri }}" alt="Barcode Toko" style="width: 320px; height: 320px; max-width: 100%;">

        <div class="meta">
            <div><strong>ID Toko:</strong> {{ $vendor->idvendor }}</div>
            <div><strong>Latitude:</strong> {{ $payload['latitude'] ?? '-' }}</div>
            <div><strong>Longitude:</strong> {{ $payload['longitude'] ?? '-' }}</div>
            <div><strong>Accuracy:</strong> {{ isset($payload['accuracy']) ? $payload['accuracy'].' m' : '-' }}</div>
        </div>

        <div class="actions">
            <button class="btn btn-primary" onclick="window.print()">Cetak</button>
            <button class="btn btn-secondary" onclick="window.close()">Tutup</button>
        </div>
    </div>
</body>
</html>