<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Tiket Antrian</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <style>
        * { box-sizing: border-box; }
        body {
            margin: 0;
            min-height: 100vh;
            display: grid;
            place-items: center;
            font-family: 'Inter', sans-serif;
            background: linear-gradient(145deg, #07111f, #13213a);
            color: #edf4ff;
            padding: 24px;
        }

        .ticket {
            width: min(100%, 620px);
            background: rgba(12, 20, 37, 0.94);
            border: 1px solid rgba(255,255,255,0.10);
            border-radius: 30px;
            padding: 32px;
            box-shadow: 0 30px 90px rgba(0,0,0,0.40);
        }

        .number {
            font-size: clamp(48px, 8vw, 88px);
            font-weight: 800;
            margin: 6px 0 16px;
        }

        .row {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 16px;
        }

        .box {
            padding: 18px;
            border-radius: 18px;
            background: rgba(255,255,255,0.04);
            border: 1px solid rgba(255,255,255,0.06);
        }

        .label { color: #8ea2c7; font-size: 13px; text-transform: uppercase; letter-spacing: .08em; }
        .value { margin-top: 10px; font-size: 20px; font-weight: 700; }
        .status { display: inline-flex; padding: 10px 16px; border-radius: 999px; background: #ffcc33; color: #1b1600; font-weight: 800; }
        .footer { margin-top: 22px; color: #8ea2c7; font-size: 13px; text-align: center; }

        @media (max-width: 640px) {
            .row { grid-template-columns: 1fr; }
        }
    </style>
</head>
<body>
    <div class="ticket">
        <div class="label">Tiket Digital</div>
        <div class="number">{{ $antrian->nomor_display }}</div>
        <div class="status">{{ ucfirst($antrian->status) }}</div>

        <div class="row" style="margin-top: 22px;">
            <div class="box">
                <div class="label">Nama</div>
                <div class="value">{{ $antrian->nama }}</div>
            </div>
            <div class="box">
                <div class="label">Waktu Daftar</div>
                <div class="value">{{ $antrian->created_at?->format('d M Y, H:i') }}</div>
            </div>
        </div>

        <div class="box" style="margin-top: 16px;">
            <div class="label">Status Antrian</div>
            <div class="value">{{ ucfirst($antrian->status) }}</div>
        </div>

        <div class="footer">Simpan halaman ini sebagai bukti nomor antrian Anda.</div>
    </div>
</body>
</html>