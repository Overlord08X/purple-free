<?php

namespace App\Http\Controllers;

use App\Models\Antrian;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;

class AntrianController extends Controller
{
    private const CACHE_VERSION = 'antrian.version';
    private const CACHE_SNAPSHOT = 'antrian.snapshot';
    private const CACHE_CURRENT = 'antrian.current_called_id';
    private const CACHE_ANNOUNCEMENT = 'antrian.announcement';

    public function guest()
    {
        return view('antrian.guest', [
            'nextNumber' => $this->nextNumberPreview(),
        ]);
    }

    public function admin()
    {
        return view('antrian.admin', [
            'snapshot' => $this->snapshot(),
        ]);
    }

    public function papan()
    {
        return view('antrian.papan', [
            'snapshot' => $this->snapshot(),
        ]);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'nama' => ['required', 'string', 'max:255'],
        ]);

        $antrian = Cache::lock('antrian.store.number', 10)->block(5, function () use ($validated) {
            return DB::transaction(function () use ($validated) {
                $lastNumber = (int) Antrian::query()->max('nomor_antrian');

                return Antrian::create([
                    'nomor_antrian' => $lastNumber + 1,
                    'nama' => $validated['nama'],
                    'status' => 'menunggu',
                ]);
            });
        });

        $this->refreshCache();

        return view('antrian.ticket', [
            'antrian' => $antrian->fresh(),
        ]);
    }

    public function panggil(Request $request): JsonResponse
    {
        if ($request->filled('id')) {
            $antrian = Antrian::query()
                ->whereIn('status', ['menunggu', 'dipanggil'])
                ->find($request->integer('id'));
        } else {
            $antrian = Antrian::query()
                ->where('status', 'menunggu')
                ->orderBy('created_at')
                ->first();
        }

        if (! $antrian) {
            return response()->json([
                'message' => 'Tidak ada antrian yang bisa dipanggil.',
            ], 404);
        }

        $antrian->forceFill([
            'status' => 'dipanggil',
            'dipanggil_pada' => now(),
        ])->save();

        Cache::forever(self::CACHE_CURRENT, $antrian->id);
        Cache::forever(self::CACHE_ANNOUNCEMENT, [
            'type' => 'call',
            'queue_id' => $antrian->id,
            'sent_at' => now()->toIso8601String(),
        ]);

        $this->refreshCache();

        return response()->json([
            'message' => 'Antrian berhasil dipanggil.',
            'data' => $this->formatQueue($antrian->fresh()),
        ]);
    }

    public function ulang(Request $request): JsonResponse
    {
        $antrian = $this->resolveQueueTarget($request, ['dipanggil']);

        if (! $antrian) {
            return response()->json([
                'message' => 'Tidak ada antrian yang bisa dipanggil ulang.',
            ], 404);
        }

        Cache::forever(self::CACHE_CURRENT, $antrian->id);
        Cache::forever(self::CACHE_ANNOUNCEMENT, [
            'type' => 'repeat',
            'queue_id' => $antrian->id,
            'sent_at' => now()->toIso8601String(),
        ]);

        $this->refreshCache();

        return response()->json([
            'message' => 'Antrian berhasil dipanggil ulang.',
            'data' => $this->formatQueue($antrian->fresh()),
        ]);
    }

    public function terlambat(Request $request): JsonResponse
    {
        $antrian = $this->resolveQueueTarget($request, ['dipanggil', 'menunggu']);

        if (! $antrian) {
            return response()->json([
                'message' => 'Tidak ada antrian yang bisa ditandai terlambat.',
            ], 404);
        }

        $antrian->forceFill([
            'status' => 'terlambat',
        ])->save();

        if ((int) Cache::get(self::CACHE_CURRENT) === (int) $antrian->id) {
            Cache::forget(self::CACHE_CURRENT);
        }

        $this->refreshCache();

        return response()->json([
            'message' => 'Antrian berhasil ditandai terlambat.',
            'data' => $this->formatQueue($antrian->fresh()),
        ]);
    }

    public function selesai(Request $request): JsonResponse
    {
        $antrian = $this->resolveQueueTarget($request, ['dipanggil']);

        if (! $antrian) {
            return response()->json([
                'message' => 'Tidak ada antrian yang bisa diselesaikan.',
            ], 404);
        }

        $antrian->forceFill([
            'status' => 'selesai',
            'selesai_pada' => now(),
        ])->save();

        if ((int) Cache::get(self::CACHE_CURRENT) === (int) $antrian->id) {
            Cache::forget(self::CACHE_CURRENT);
        }

        $this->refreshCache();

        return response()->json([
            'message' => 'Antrian berhasil diselesaikan.',
            'data' => $this->formatQueue($antrian->fresh()),
        ]);
    }

    public function stream()
    {
        $headers = [
            'Content-Type' => 'text/event-stream; charset=utf-8',
            'Cache-Control' => 'no-cache, no-store, must-revalidate',
            'Pragma' => 'no-cache',
            'Expires' => '0',
            'X-Accel-Buffering' => 'no',
            'Connection' => 'keep-alive',
        ];

        return response()->stream(function (): void {
            @ini_set('output_buffering', 'off');
            @ini_set('zlib.output_compression', '0');
            @set_time_limit(0);
            @ignore_user_abort(true);

            $lastVersion = null;
            $heartbeatCount = 0;

            while (! connection_aborted()) {
                $version = (int) Cache::get(self::CACHE_VERSION, 0);

                if ($lastVersion !== $version) {
                    $payload = $this->snapshot();

                    echo "event: queue-update\n";
                    echo 'data: ' . json_encode($payload, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES) . "\n\n";

                    $lastVersion = $version;
                    $heartbeatCount = 0;
                } else {
                    // Heartbeat every second to keep connection alive
                    echo ": heartbeat {$heartbeatCount}\n\n";
                    $heartbeatCount++;
                }

                if (ob_get_level() > 0) {
                    @ob_flush();
                }

                @flush();
                sleep(1);
            }
        }, 200, $headers);
    }

    private function snapshot(): array
    {
        $cached = Cache::get(self::CACHE_SNAPSHOT);

        if (is_array($cached) && isset($cached['stats'], $cached['queues'])) {
            return $cached;
        }

        return $this->refreshCache();
    }

    private function refreshCache(): array
    {
        $waiting = Antrian::query()
            ->where('status', 'menunggu')
            ->orderBy('created_at')
            ->get();

        $called = Antrian::query()
            ->where('status', 'dipanggil')
            ->orderByDesc('dipanggil_pada')
            ->orderByDesc('id')
            ->get();

        $done = Antrian::query()
            ->where('status', 'selesai')
            ->orderByDesc('selesai_pada')
            ->orderByDesc('id')
            ->get();

        $late = Antrian::query()
            ->where('status', 'terlambat')
            ->orderByDesc('updated_at')
            ->orderByDesc('id')
            ->get();

        $currentId = Cache::get(self::CACHE_CURRENT);
        $current = null;

        if ($currentId) {
            $current = Antrian::find($currentId);
        }

        if (! $current) {
            $current = $called->first();

            if ($current) {
                Cache::forever(self::CACHE_CURRENT, $current->id);
            } else {
                Cache::forget(self::CACHE_CURRENT);
            }
        }

        $announcement = Cache::get(self::CACHE_ANNOUNCEMENT);
        if (! is_array($announcement)) {
            $announcement = null;
        }

        $snapshot = [
            'stats' => [
                'total' => Antrian::count(),
                'menunggu' => $waiting->count(),
                'dipanggil' => $called->count(),
                'selesai' => $done->count(),
                'terlambat' => $late->count(),
            ],
            'current' => $current ? $this->formatQueue($current) : null,
            'next' => $waiting->first() ? $this->formatQueue($waiting->first()) : null,
            'queues' => [
                'menunggu' => $waiting->map(fn (Antrian $antrian) => $this->formatQueue($antrian))->values(),
                'dipanggil' => $called->map(fn (Antrian $antrian) => $this->formatQueue($antrian))->values(),
                'selesai' => $done->map(fn (Antrian $antrian) => $this->formatQueue($antrian))->values(),
                'terlambat' => $late->map(fn (Antrian $antrian) => $this->formatQueue($antrian))->values(),
            ],
            'announcement' => $announcement,
            'generated_at' => now()->toIso8601String(),
        ];

        Cache::forever(self::CACHE_SNAPSHOT, $snapshot);
        Cache::forever(self::CACHE_VERSION, ((int) Cache::get(self::CACHE_VERSION, 0)) + 1);

        return $snapshot;
    }

    private function formatQueue(Antrian $antrian): array
    {
        return [
            'id' => $antrian->id,
            'nomor_antrian' => (int) $antrian->nomor_antrian,
            'nomor_display' => $antrian->nomor_display,
            'nama' => $antrian->nama,
            'status' => $antrian->status,
            'status_label' => $antrian->status_label,
            'created_at' => $antrian->created_at?->format('d M Y, H:i') ?? '-',
            'dipanggil_pada' => $antrian->dipanggil_pada?->format('d M Y, H:i') ?? '-',
            'selesai_pada' => $antrian->selesai_pada?->format('d M Y, H:i') ?? '-',
        ];
    }

    private function resolveQueueTarget(Request $request, array $statuses): ?Antrian
    {
        if ($request->filled('id')) {
            return Antrian::query()
                ->whereIn('status', $statuses)
                ->find($request->integer('id'));
        }

        $currentId = Cache::get(self::CACHE_CURRENT);

        if ($currentId) {
            $current = Antrian::find($currentId);

            if ($current && in_array($current->status, $statuses, true)) {
                return $current;
            }
        }

        return Antrian::query()
            ->whereIn('status', $statuses)
            ->orderBy('status')
            ->orderBy('created_at')
            ->first();
    }

    private function nextNumberPreview(): int
    {
        return ((int) Antrian::max('nomor_antrian')) + 1;
    }

    public function generateAnnouncement(Request $request)
    {
        $nomor = $request->integer('nomor', 0);
        $nama = $request->string('nama', 'Tamu');

        if ($nomor <= 0) {
            return response()->json(['error' => 'nomor tidak valid'], 400);
        }

        // Format nomor dengan padding 3 digit
        $nomorFormat = str_pad($nomor, 3, '0', STR_PAD_LEFT);

        // Buat teks pengumuman dalam bahasa Indonesia
        $text = "nomor antrian {$nomorFormat}, {$nama}, silahkan memasuki ruangan";

        // Buat cache key unik untuk file
        $cacheKey = 'antrian_audio_' . md5($text);
        $audioFile = storage_path('app/antrian_audio/' . $cacheKey . '.mp3');
        $audioDir = dirname($audioFile);
        // Cek apakah file sudah ada
        if (file_exists($audioFile)) {
            return response()->file($audioFile, [
                'Content-Type' => 'audio/mpeg',
                'Cache-Control' => 'public, max-age=3600',
            ]);
        }

        // If Google Cloud TTS API key is provided, prefer using it for natural Indonesian voice
        $googleApiKey = env('GOOGLE_TTS_API_KEY');
        if ($googleApiKey) {
            try {
                if (!is_dir($audioDir)) {
                    mkdir($audioDir, 0755, true);
                }

                $payload = [
                    'input' => ['text' => $text],
                    'voice' => [
                        'languageCode' => 'id-ID',
                        // name can be adjusted; WaveNet voices are preferred when available
                        'name' => 'id-ID-Wavenet-A',
                    ],
                    'audioConfig' => [
                        'audioEncoding' => 'MP3',
                        'speakingRate' => 1.0,
                        'pitch' => 0.0,
                    ],
                ];

                $url = 'https://texttospeech.googleapis.com/v1/text:synthesize?key=' . urlencode($googleApiKey);
                $opts = [
                    'http' => [
                        'method' => 'POST',
                        'header' => "Content-Type: application/json\r\n",
                        'content' => json_encode($payload),
                        'timeout' => 10,
                    ],
                ];

                $context = stream_context_create($opts);
                $result = @file_get_contents($url, false, $context);

                if ($result !== false) {
                    $json = json_decode($result, true);
                    if (isset($json['audioContent']) && $decoded = base64_decode($json['audioContent'])) {
                        file_put_contents($audioFile, $decoded);
                        return response()->file($audioFile, [
                            'Content-Type' => 'audio/mpeg',
                            'Cache-Control' => 'public, max-age=3600',
                        ]);
                    }
                }
            } catch (\Throwable $e) {
                // Log and fall back to local espeak
                \Log::error('Google TTS failed: ' . $e->getMessage());
            }
        }

        // Buat direktori jika belum ada
        if (!is_dir($audioDir)) {
            mkdir($audioDir, 0755, true);
        }

        // Generate WAV file menggunakan espeak
        $wavFile = $audioDir . '/' . $cacheKey . '.wav';
        $cmd = sprintf(
            'espeak -v id -s 120 -a 200 %s -w %s 2>/dev/null',
            escapeshellarg($text),
            escapeshellarg($wavFile)
        );

        $output = shell_exec($cmd);

        if (!file_exists($wavFile)) {
            return response()->json(['error' => 'Gagal generate suara'], 500);
        }

        // Convert WAV ke MP3 menggunakan ffmpeg
        $ffmpegCmd = sprintf(
            'ffmpeg -i %s -q:a 9 -y %s 2>/dev/null',
            escapeshellarg($wavFile),
            escapeshellarg($audioFile)
        );

        shell_exec($ffmpegCmd);

        // Hapus WAV file yang sudah tidak perlu
        if (file_exists($wavFile)) {
            unlink($wavFile);
        }

        if (!file_exists($audioFile)) {
            return response()->json(['error' => 'Gagal convert ke MP3'], 500);
        }

        return response()->file($audioFile, [
            'Content-Type' => 'audio/mpeg',
            'Cache-Control' => 'public, max-age=3600',
        ]);
    }
}