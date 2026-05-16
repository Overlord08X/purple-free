<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Papan Antrian</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800;900&display=swap" rel="stylesheet">
    <style>
        * { box-sizing: border-box; }
        body {
            margin: 0;
            min-height: 100vh;
            overflow: hidden;
            font-family: 'Inter', sans-serif;
            color: #fff;
            background:
                radial-gradient(circle at top left, rgba(93, 214, 255, 0.18), transparent 28%),
                radial-gradient(circle at bottom right, rgba(124, 140, 255, 0.24), transparent 30%),
                linear-gradient(135deg, #050b16, #0e1b31 50%, #09101d);
        }

        .wrap {
            min-height: 100vh;
            display: grid;
            grid-template-rows: auto 1fr auto;
            padding: 22px 28px;
            gap: 20px;
        }

        .topbar, .bottombar {
            display: flex;
            justify-content: space-between;
            align-items: center;
            gap: 20px;
        }

        .brand {
            display: flex;
            align-items: center;
            gap: 14px;
            font-weight: 800;
            letter-spacing: .04em;
        }

        .brand-mark {
            width: 58px;
            height: 58px;
            border-radius: 18px;
            background: linear-gradient(135deg, #5dd6ff, #7c8cff);
            box-shadow: 0 18px 50px rgba(93, 214, 255, 0.22);
        }

        .clock {
            font-size: clamp(22px, 3vw, 34px);
            font-weight: 800;
        }

        .content {
            display: grid;
            grid-template-columns: 1.3fr .7fr;
            gap: 20px;
            align-items: stretch;
        }

        .hero, .side {
            background: rgba(10, 18, 34, 0.72);
            border: 1px solid rgba(255,255,255,0.09);
            border-radius: 28px;
            backdrop-filter: blur(16px);
            box-shadow: 0 30px 90px rgba(0,0,0,0.28);
        }

        .hero {
            padding: 28px;
            display: grid;
            place-items: center;
            text-align: center;
            position: relative;
            overflow: hidden;
        }

        .hero::before {
            content: '';
            position: absolute;
            inset: 0;
            background: radial-gradient(circle at center, rgba(255,255,255,0.05), transparent 60%);
        }

        .caption { text-transform: uppercase; letter-spacing: .18em; color: #8ea2c7; font-size: 14px; }
        .queue-number {
            font-size: clamp(88px, 18vw, 220px);
            font-weight: 900;
            line-height: .9;
            margin: 14px 0;
            text-shadow: 0 12px 45px rgba(93, 214, 255, 0.15);
        }
        .name {
            font-size: clamp(28px, 5vw, 72px);
            font-weight: 800;
        }
        .status-pill {
            margin-top: 18px;
            display: inline-flex;
            align-items: center;
            padding: 16px 24px;
            border-radius: 999px;
            font-size: 22px;
            font-weight: 800;
        }

        .side {
            padding: 24px;
            display: grid;
            grid-template-rows: auto auto 1fr;
            gap: 18px;
        }

        .card-stat {
            padding: 18px;
            border-radius: 20px;
            background: rgba(255,255,255,0.05);
            border: 1px solid rgba(255,255,255,0.08);
        }

        .big { font-size: 54px; font-weight: 900; line-height: 1; }
        .label { color: #8ea2c7; text-transform: uppercase; letter-spacing: .08em; font-size: 13px; }

        .next-wrap {
            display: grid;
            place-items: center;
            padding: 24px;
            border-radius: 24px;
            background: linear-gradient(135deg, rgba(93, 214, 255, 0.12), rgba(124, 140, 255, 0.10));
        }

        .next-number {
            font-size: clamp(44px, 8vw, 110px);
            font-weight: 900;
            margin: 6px 0 0;
        }

        .status-warning { background: #ffcf33; color: #111; }
        .status-primary { background: #3c82ff; }
        .status-success { background: #2ecc71; }
        .status-danger { background: #ff5b5b; }

        .sound-banner {
            position: fixed;
            left: 50%;
            bottom: 24px;
            transform: translateX(-50%);
            z-index: 30;
            display: flex;
            align-items: center;
            gap: 12px;
            padding: 14px 18px;
            border-radius: 999px;
            background: rgba(6, 12, 24, 0.88);
            border: 1px solid rgba(255,255,255,0.12);
            box-shadow: 0 18px 50px rgba(0,0,0,0.35);
        }

        .sound-banner button {
            border: 0;
            border-radius: 999px;
            padding: 10px 16px;
            font-weight: 800;
            color: #07111f;
            background: linear-gradient(135deg, #5dd6ff, #7c8cff);
        }

        .sound-banner .status {
            color: #dce7f9;
            font-size: 14px;
            font-weight: 600;
        }

        .sound-banner.hidden {
            display: none;
        }

        .pulse {
            animation: pulse 1.2s ease-in-out infinite;
        }

        @keyframes pulse {
            0%, 100% { transform: scale(1); }
            50% { transform: scale(1.02); }
        }

        @media (max-width: 1200px) {
            body { overflow: auto; }
            .content { grid-template-columns: 1fr; }
            .wrap { padding: 16px; }
        }
    </style>
</head>
<body>
    <div class="wrap">
        <div class="topbar">
            <div class="brand">
                <div class="brand-mark"></div>
                <div>
                    <div style="font-size: 22px;">PAPAN ANTRIAN DIGITAL</div>
                    <div style="color: #8ea2c7; font-size: 14px;">Realtime SSE, Speech Synthesis, dan notifikasi audio</div>
                </div>
            </div>
            <div class="clock" id="papanClock">--:--:--</div>
        </div>

        <div class="content">
            <section class="hero">
                <div style="position: relative; z-index: 1; width: 100%;">
                    <div class="caption">Nomor Dipanggil</div>
                    <div class="queue-number pulse" id="papanNumber">{{ $snapshot['current']['nomor_display'] ?? '--' }}</div>
                    <div class="name" id="papanName">{{ $snapshot['current']['nama'] ?? 'Menunggu panggilan berikutnya' }}</div>
                    <div class="status-pill {{ $snapshot['current'] ? match($snapshot['current']['status']) {
                        'dipanggil' => 'status-primary',
                        'selesai' => 'status-success',
                        'terlambat' => 'status-danger',
                        default => 'status-warning',
                    } : 'status-warning' }}" id="papanStatusBadge">{{ $snapshot['current']['status_label'] ?? 'Menunggu' }}</div>
                </div>
            </section>

            <aside class="side">
                <div class="card-stat">
                    <div class="label">Status Saat Ini</div>
                    <div style="font-size: 28px; font-weight: 800; margin-top: 8px;" id="papanStatus">{{ $snapshot['current']['status_label'] ?? 'Menunggu' }}</div>
                </div>

                <div class="next-wrap">
                    <div class="label">Nomor Berikutnya</div>
                    <div class="next-number" id="papanNext">{{ $snapshot['next']['nomor_display'] ?? '--' }}</div>
                </div>

                <div class="card-stat">
                    <div class="label">Catatan</div>
                    <div style="font-size: 18px; font-weight: 600; line-height: 1.6; margin-top: 10px; color: #dce7f9;">
                        Dengarkan suara panggilan dan tunggu nomor Anda tampil di layar.
                    </div>
                </div>
            </aside>
        </div>

        <div class="bottombar">
            <div class="card-stat" style="flex: 1;">
                <div class="label">Antrian Aktif</div>
                <div class="big" id="papanStatusCount">{{ $snapshot['stats']['dipanggil'] ?? 0 }}</div>
            </div>
            <div class="card-stat" style="flex: 1;">
                <div class="label">Menunggu</div>
                <div class="big" id="papanWaitingCount">{{ $snapshot['stats']['menunggu'] ?? 0 }}</div>
            </div>
            <div class="card-stat" style="flex: 1;">
                <div class="label">Selesai</div>
                <div class="big" id="papanDoneCount">{{ $snapshot['stats']['selesai'] ?? 0 }}</div>
            </div>
        </div>
    </div>

    <script src="{{ asset('assets/js/antrian.js') }}"></script>
    <script>
        window.addEventListener('DOMContentLoaded', () => {
            window.antrianBoard = initAntrianRealtime({
                mode: 'papan',
                eventSourceUrl: '{{ url('/sse/antrian') }}',
                audioUrl: '{{ asset('assets/sound/dragon-studio-censor-beep-3-372460.mp3') }}',
                audioGenerationUrl: '{{ url('/antrian/audio') }}',
                selectors: {
                    papanNumber: '#papanNumber',
                    papanName: '#papanName',
                    papanNext: '#papanNext',
                    papanStatus: '#papanStatus',
                    papanStatusBadge: '#papanStatusBadge',
                    papanClock: '#papanClock',
                }
            });

            // Setup sound banner after antrianBoard is ready
            const banner = document.getElementById('soundBanner');
            const button = document.getElementById('enableSoundButton');

            if (button && window.antrianBoard) {
                const unlock = async () => {
                    console.log('Unlocking sound...');
                    await window.antrianBoard.unlockSound();
                    console.log('Sound unlocked');
                    if (banner) {
                        banner.classList.add('hidden');
                    }
                };

                button.addEventListener('click', unlock);
                document.addEventListener('pointerdown', unlock, { once: true });
            }
        });
    </script>

    <div class="sound-banner" id="soundBanner">
        <div class="status">Suara belum aktif. Klik tombol ini untuk mengizinkan audio notification (beep) saat antrian dipanggil.</div>
        <button type="button" id="enableSoundButton">Aktifkan Suara</button>
    </div>
</body>
</html>