<?php

namespace App\Http\Controllers\project;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Models\Pesanan;
use App\Models\DetailPesanan;
use App\Models\Menu;
use App\Models\Vendor;
use Illuminate\Support\Facades\Log;
use Endroid\QrCode\QrCode;
use Endroid\QrCode\Writer\PngWriter;

class projectController extends Controller
{
    public function index()
    {
        return view('project.index');
    }

    public function kota()
    {
        return view('project.kota');
    }

    public function scanner()
    {
        return view('project.scanner');
    }

    public function customer(Request $request, $idpesanan = null)
    {
        $penjualan = null;
        $details = [];
        $qrCodeDataUri = null;

        $orderId = $idpesanan ?: $request->query('idpesanan');

        if ($orderId) {
            // Query dari table penjualan (hasil transaksi kantin)
            $penjualan = DB::table('penjualan')->where('idpenjualan', $orderId)->first();

            if ($penjualan) {
                // Fallback sync: if callback missed, re-check payment status to Midtrans.
                if ((int) $penjualan->status_bayar !== 1 && !empty($penjualan->order_id)) {
                    try {
                        \Midtrans\Config::$serverKey = config('midtrans.server_key') ?: env('MIDTRANS_SERVER_KEY');
                        \Midtrans\Config::$isProduction = false;
                        \Midtrans\Config::$curlOptions = [
                            CURLOPT_SSL_VERIFYPEER => false,
                            CURLOPT_SSL_VERIFYHOST => 0,
                        ];

                        $status = $this->fetchMidtransStatus((string) $penjualan->order_id);

                        Log::info('CUSTOMER PAGE MIDTRANS STATUS', [
                            'idpenjualan' => $penjualan->idpenjualan,
                            'order_id' => $penjualan->order_id,
                            'transaction_status' => $status['transaction_status'] ?? null,
                            'status_code' => $status['status_code'] ?? null,
                        ]);

                        if (in_array($status['transaction_status'] ?? null, ['settlement', 'capture'])) {
                            DB::table('penjualan')
                                ->where('idpenjualan', $penjualan->idpenjualan)
                                ->update([
                                    'status_bayar' => 1,
                                    'transaction_id' => $status['transaction_id'] ?? $penjualan->transaction_id,
                                    'payment_type' => $status['payment_type'] ?? $penjualan->payment_type,
                                    'payment_details' => json_encode($status),
                                ]);

                            $penjualan = DB::table('penjualan')->where('idpenjualan', $orderId)->first();
                        }
                    } catch (\Throwable $e) {
                        Log::warning('CUSTOMER PAGE MIDTRANS SYNC FAILED', [
                            'idpenjualan' => $penjualan->idpenjualan,
                            'order_id' => $penjualan->order_id,
                            'error' => $e->getMessage(),
                        ]);
                    }
                }

                // Get detail penjualan dengan info barang/menu
                $details = DB::table('penjualan_detail')
                    ->leftJoin('barang', 'penjualan_detail.idbarang', '=', 'barang.idbarang')
                    ->leftJoin('menu', 'penjualan_detail.idmenu', '=', 'menu.idmenu')
                    ->where('penjualan_detail.idpenjualan', $orderId)
                    ->select(
                        'penjualan_detail.*',
                        DB::raw('COALESCE(barang.nama_barang, menu.nama_menu) as nama_item'),
                        'barang.nama_barang',
                        'menu.nama_menu'
                    )
                    ->get();

                // Generate QR dengan simple idpenjualan
                $qrCode = QrCode::create((string) $penjualan->idpenjualan)
                    ->setSize(320)
                    ->setMargin(12);

                $writer = new PngWriter();
                $qrCodeDataUri = $writer->write($qrCode)->getDataUri();
            }
        }

        return view('project.customer', compact('penjualan', 'details', 'qrCodeDataUri', 'orderId'));
    }

    public function vendor()
    {
        $vendors = Vendor::orderBy('nama_vendor')->get();
        $menus = Menu::with('vendor')->orderBy('nama_menu')->get();

        return view('project.vendor', compact('vendors', 'menus'));
    }

    public function storeVendor(Request $request)
    {
        $request->validate([
            'nama_vendor' => 'required|string|max:255',
        ]);

        Vendor::create([
            'nama_vendor' => $request->nama_vendor,
        ]);

        return back()->with('success', 'Vendor berhasil ditambahkan.');
    }

    public function storeMenu(Request $request)
    {
        $request->validate([
            'vendor_id' => 'required|exists:vendor,idvendor',
            'nama_menu' => 'required|string|max:255',
            'harga' => 'required|integer|min:0',
            'path_gambar' => 'nullable|image',
        ]);

        $path = null;
        if ($request->hasFile('path_gambar')) {
            $path = $request->file('path_gambar')->store('menus');
        }

        Menu::create([
            'nama_menu' => $request->nama_menu,
            'harga' => $request->harga,
            'path_gambar' => $path,
            'idvendor' => $request->vendor_id,
        ]);

        return back()->with('success', 'Menu berhasil ditambahkan ke vendor terpilih.');
    }

    public function destroyMenu($id)
    {
        Menu::findOrFail($id)->delete();

        return back()->with('success', 'Menu berhasil dihapus.');
    }

    public function vendorScanner()
    {
        $vendors = Vendor::orderBy('nama_vendor')->get();

        return view('project.vendor_scanner', compact('vendors'));
    }

    public function vendorOrder(Request $request, $idpenjualan)
    {
        $request->validate([
            'vendor_id' => 'required|exists:vendor,idvendor',
        ]);

        // Query dari table penjualan (hasil transaksi kantin), bukan pesanan lama
        $penjualan = DB::table('penjualan')
            ->where('idpenjualan', $idpenjualan)
            ->first();

        if (!$penjualan) {
            abort(404, 'Penjualan tidak ditemukan');
        }

        $vendor = Vendor::findOrFail($request->vendor_id);

        // Query detail penjualan yang match dengan vendor
        $details = DB::table('penjualan_detail')
            ->leftJoin('menu', 'penjualan_detail.idmenu', '=', 'menu.idmenu')
            ->where('penjualan_detail.idpenjualan', $idpenjualan)
            ->where('penjualan_detail.item_type', 'menu')
            ->where('menu.idvendor', $vendor->idvendor)
            ->select(
                'penjualan_detail.idmenu',
                'menu.nama_menu',
                'penjualan_detail.jumlah',
                // penjualan_detail.harga doesn't exist; use menu.harga as harga
                DB::raw('menu.harga as harga'),
                'penjualan_detail.subtotal'
            )
            ->get();

        $items = $details->map(function ($detail) {
            return [
                'idmenu' => (int) $detail->idmenu,
                'nama_menu' => $detail->nama_menu ?? '-',
                'jumlah' => (int) $detail->jumlah,
                'harga' => (int) $detail->harga,
                'subtotal' => (int) $detail->subtotal,
            ];
        });

        return response()->json([
            // alias for compatibility: idpesanan expected by scanner UI
            'idpesanan' => (int) $penjualan->idpenjualan,
            'idpenjualan' => (int) $penjualan->idpenjualan,
            'status_bayar' => (int) $penjualan->status_bayar,
            'status_text' => $penjualan->status_bayar == 1 ? 'Lunas' : 'Pending',
            'vendor' => [
                'idvendor' => (int) $vendor->idvendor,
                'nama_vendor' => $vendor->nama_vendor,
            ],
            'items' => $items,
            'total_vendor' => $items->sum('subtotal'),
        ]);
    }

    public function kunjunganToko()
    {
        $vendors = Vendor::orderBy('nama_vendor')->get()->map(function ($vendor) {
            $vendor->barcode_label = $vendor->barcode ?: 'TOKO-' . $vendor->idvendor;
            return $vendor;
        });

        $vendorPayload = $vendors->map(function ($vendor) {
            return [
                'idvendor' => (int) $vendor->idvendor,
                'nama_vendor' => $vendor->nama_vendor,
                'barcode' => $vendor->barcode_label,
                'latitude' => $vendor->latitude !== null ? (float) $vendor->latitude : null,
                'longitude' => $vendor->longitude !== null ? (float) $vendor->longitude : null,
                'accuracy' => $vendor->accuracy !== null ? (float) $vendor->accuracy : null,
            ];
        })->values();

        return view('project.kunjungan_toko', compact('vendors', 'vendorPayload'));
    }

    public function storeKunjunganToko(Request $request)
    {
        $validated = $request->validate([
            'vendor_id' => 'required|exists:vendor,idvendor',
            'barcode' => 'nullable|string|max:255',
            'latitude' => 'required|numeric|between:-90,90',
            'longitude' => 'required|numeric|between:-180,180',
            'accuracy' => 'required|numeric|min:0',
        ]);

        $vendor = Vendor::findOrFail($validated['vendor_id']);

        $vendor->update([
            'barcode' => $validated['barcode'] ?: ($vendor->barcode ?: 'TOKO-' . $vendor->idvendor),
            'latitude' => $validated['latitude'],
            'longitude' => $validated['longitude'],
            'accuracy' => $validated['accuracy'],
        ]);

        return back()->with('success', 'Titik awal toko berhasil disimpan.');
    }

    public function verifyKunjunganToko(Request $request)
    {
        $validated = $request->validate([
            'vendor_id' => 'required|exists:vendor,idvendor',
            'sales_latitude' => 'required|numeric|between:-90,90',
            'sales_longitude' => 'required|numeric|between:-180,180',
            'sales_accuracy' => 'required|numeric|min:0',
            'max_distance' => 'nullable|numeric|min:0',
        ]);

        $vendor = Vendor::findOrFail($validated['vendor_id']);

        if ($vendor->latitude === null || $vendor->longitude === null) {
            return response()->json([
                'status' => 'error',
                'message' => 'Titik awal toko belum diisi.',
            ], 422);
        }

        $actualDistance = $this->haversineDistance(
            (float) $vendor->latitude,
            (float) $vendor->longitude,
            (float) $validated['sales_latitude'],
            (float) $validated['sales_longitude']
        );

        $maxDistance = (float) ($validated['max_distance'] ?? 300);
        $effectiveThreshold = $maxDistance + (float) ($vendor->accuracy ?? 0) + (float) $validated['sales_accuracy'];
        $accepted = $actualDistance <= $effectiveThreshold;

        return response()->json([
            'status' => $accepted ? 'accepted' : 'rejected',
            'message' => $accepted ? 'Kunjungan diterima.' : 'Kunjungan ditolak karena di luar radius.',
            'vendor' => [
                'idvendor' => (int) $vendor->idvendor,
                'nama_vendor' => $vendor->nama_vendor,
                'barcode' => $vendor->barcode ?: 'TOKO-' . $vendor->idvendor,
                'latitude' => (float) $vendor->latitude,
                'longitude' => (float) $vendor->longitude,
                'accuracy' => (float) ($vendor->accuracy ?? 0),
            ],
            'sales' => [
                'latitude' => (float) $validated['sales_latitude'],
                'longitude' => (float) $validated['sales_longitude'],
                'accuracy' => (float) $validated['sales_accuracy'],
            ],
            'distance' => round($actualDistance, 2),
            'effective_threshold' => round($effectiveThreshold, 2),
            'max_distance' => $maxDistance,
        ]);
    }

    public function barcode($idvendor)
    {
        $vendor = Vendor::findOrFail($idvendor);

        $payload = [
            'idvendor' => (int) $vendor->idvendor,
            'nama_vendor' => $vendor->nama_vendor,
            'barcode' => $vendor->barcode ?: 'TOKO-' . $vendor->idvendor,
            'latitude' => $vendor->latitude !== null ? (float) $vendor->latitude : null,
            'longitude' => $vendor->longitude !== null ? (float) $vendor->longitude : null,
            'accuracy' => $vendor->accuracy !== null ? (float) $vendor->accuracy : null,
        ];

        $qrCode = QrCode::create(json_encode($payload, JSON_UNESCAPED_UNICODE))
            ->setSize(320)
            ->setMargin(10);

        $writer = new PngWriter();
        $qrCodeDataUri = $writer->write($qrCode)->getDataUri();

        return view('project.kunjungan_toko_barcode', compact('vendor', 'qrCodeDataUri', 'payload'));
    }

    private function fetchMidtransStatus(string $orderId): array
    {
        $isProduction = (bool) config('midtrans.is_production', false);
        $baseUrl = $isProduction
            ? 'https://api.midtrans.com'
            : 'https://api.sandbox.midtrans.com';

        $serverKey = config('midtrans.server_key') ?: env('MIDTRANS_SERVER_KEY');
        $url = $baseUrl . '/v2/' . urlencode($orderId) . '/status';

        $ch = curl_init($url);
        curl_setopt_array($ch, [
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_TIMEOUT => 15,
            CURLOPT_HTTPHEADER => [
                'Accept: application/json',
                'Authorization: Basic ' . base64_encode($serverKey . ':'),
            ],
        ]);

        $raw = curl_exec($ch);
        $err = curl_error($ch);
        curl_close($ch);

        if ($raw === false || $err) {
            throw new \RuntimeException('Failed fetch Midtrans status: ' . $err);
        }

        $decoded = json_decode($raw, true);
        if (!is_array($decoded)) {
            throw new \RuntimeException('Invalid Midtrans status response');
        }

        return $decoded;
    }

    private function haversineDistance(float $lat1, float $lng1, float $lat2, float $lng2): float
    {
        $earthRadius = 6371000;

        $dLat = deg2rad($lat2 - $lat1);
        $dLng = deg2rad($lng2 - $lng1);

        $a = sin($dLat / 2) * sin($dLat / 2)
            + cos(deg2rad($lat1)) * cos(deg2rad($lat2))
            * sin($dLng / 2) * sin($dLng / 2);

        $c = 2 * atan2(sqrt($a), sqrt(1 - $a));

        return $earthRadius * $c;
    }
}
