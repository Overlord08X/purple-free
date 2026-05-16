<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Ambil Antrian</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="{{ asset('assets/vendors/css/vendor.bundle.base.css') }}">
    <style>
        :root {
            --bg-1: #07111f;
            --bg-2: #0e1a30;
            --card: rgba(10, 18, 34, 0.84);
            --stroke: rgba(255, 255, 255, 0.10);
            --text: #e5eefc;
            --muted: #8ea2c7;
            --accent: #5dd6ff;
            --accent-2: #7c8cff;
        }

        * { box-sizing: border-box; }
        body {
            margin: 0;
            font-family: 'Inter', sans-serif;
            color: var(--text);
            min-height: 100vh;
            background:
                radial-gradient(circle at top left, rgba(93, 214, 255, 0.20), transparent 32%),
                radial-gradient(circle at bottom right, rgba(124, 140, 255, 0.22), transparent 30%),
                linear-gradient(135deg, var(--bg-1), var(--bg-2));
        }

        .shell {
            min-height: 100vh;
            display: grid;
            place-items: center;
            padding: 24px;
        }

        .card-shell {
            width: min(100%, 920px);
            background: var(--card);
            border: 1px solid var(--stroke);
            border-radius: 28px;
            box-shadow: 0 30px 80px rgba(0, 0, 0, 0.35);
            overflow: hidden;
            backdrop-filter: blur(18px);
        }

        .grid {
            display: grid;
            grid-template-columns: 1.15fr 0.85fr;
        }

        .hero, .form-panel { padding: 32px; }
        .eyebrow {
            display: inline-flex;
            gap: 8px;
            align-items: center;
            padding: 10px 14px;
            border-radius: 999px;
            background: rgba(93, 214, 255, 0.10);
            color: var(--accent);
            font-size: 13px;
            font-weight: 700;
            letter-spacing: .06em;
            text-transform: uppercase;
        }

        h1 { font-size: clamp(32px, 4vw, 56px); line-height: 1.02; margin: 18px 0 14px; }
        p { color: var(--muted); line-height: 1.7; }

        .next-box {
            margin-top: 28px;
            padding: 22px;
            border-radius: 24px;
            background: linear-gradient(135deg, rgba(93, 214, 255, 0.14), rgba(124, 140, 255, 0.12));
            border: 1px solid rgba(255,255,255,0.08);
        }

        .next-number {
            font-size: 64px;
            font-weight: 800;
            letter-spacing: .04em;
            margin: 0;
        }

        label {
            display: block;
            font-weight: 600;
            margin-bottom: 10px;
        }

        input {
            width: 100%;
            border: 1px solid rgba(255,255,255,0.10);
            background: rgba(255,255,255,0.05);
            color: var(--text);
            border-radius: 18px;
            padding: 18px 20px;
            font-size: 16px;
            outline: none;
        }

        input::placeholder { color: rgba(229, 238, 252, 0.42); }

        .btn-primary {
            width: 100%;
            border: 0;
            padding: 18px 20px;
            border-radius: 18px;
            font-weight: 800;
            background: linear-gradient(135deg, var(--accent), var(--accent-2));
            color: #06101d;
            box-shadow: 0 18px 40px rgba(93, 214, 255, 0.22);
        }

        .hint { font-size: 13px; color: var(--muted); }

        @media (max-width: 900px) {
            .grid { grid-template-columns: 1fr; }
        }
    </style>
</head>
<body>
    <div class="shell">
        <div class="card-shell">
            <div class="grid">
                <section class="hero">
                    <span class="eyebrow">Sistem Antrian Digital</span>
                    <h1>Ambil nomor antrian dengan cepat, tanpa login.</h1>
                    <p>Isi nama Anda, ambil nomor antrian, lalu tiket digital akan terbuka otomatis pada tab baru.</p>

                    <div class="next-box">
                        <div class="hint">Nomor antrian berikutnya</div>
                        <p class="next-number">{{ str_pad((string) $nextNumber, 3, '0', STR_PAD_LEFT) }}</p>
                    </div>
                </section>

                <section class="form-panel">
                    <form method="POST" action="{{ route('antrian.store') }}" target="_blank">
                        @csrf
                        <div class="mb-4">
                            <label for="nama">Nama Lengkap</label>
                            <input type="text" id="nama" name="nama" placeholder="Contoh: Rafi Ahmad" required autofocus>
                        </div>

                        <button type="submit" class="btn-primary">Ambil Antrian</button>

                        <div class="hint mt-3">Pastikan browser Anda mengizinkan tab baru agar tiket digital muncul otomatis.</div>
                    </form>
                </section>
            </div>
        </div>
    </div>
</body>
</html>