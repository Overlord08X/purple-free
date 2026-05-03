<?php

namespace App\Http\Controllers\payment;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Vendor;
use App\Models\Menu;
use App\Models\Pesanan;
use App\Models\DetailPesanan;
use Illuminate\Support\Facades\DB;
use Endroid\QrCode\QrCode;
use Endroid\QrCode\Writer\PngWriter;
use Illuminate\Support\Facades\Log;

class pesananController extends Controller
{
    public function index()
    {
        $vendors = Vendor::all();
        return view('pesanan.index', compact('vendors'));
    }

    public function getMenus($vendorId)
    {
        return response()->json(Menu::where('idvendor', $vendorId)->get());
    }

    public function store(Request $request)
    {
        $request->validate([
            'vendor_id' => 'required|exists:vendor,idvendor',
            'items' => 'required|array',
            'items.*.menu_id' => 'required|exists:menu,idmenu',
            'items.*.quantity' => 'required|integer|min:1',
            'items.*.catatan' => 'nullable|string',
        ]);

        DB::beginTransaction();

        try {
            $guestName = 'Guest_' . str_pad(Pesanan::count() + 1, 7, '0', STR_PAD_LEFT);

            $total = 0;
            foreach ($request->items as $item) {
                $menu = Menu::findOrFail($item['menu_id']);
                $total += $menu->harga * $item['quantity'];
            }

            $pesanan = Pesanan::create([
                'nama' => $guestName,
                'total' => $total,
                'status_bayar' => 0,
                'metode_bayar' => $request->metode_bayar ?? 1,
            ]);

            foreach ($request->items as $item) {
                $menu = Menu::findOrFail($item['menu_id']);

                DetailPesanan::create([
                    'idmenu' => $item['menu_id'],
                    'idpesanan' => $pesanan->idpesanan,
                    'jumlah' => $item['quantity'],
                    'harga' => $menu->harga,
                    'subtotal' => $menu->harga * $item['quantity'],
                    'catatan' => $item['catatan'] ?? null,
                ]);
            }

            DB::commit();

            return redirect()->route('pesanan.payment', $pesanan->idpesanan);
        } catch (\Exception $e) {
            DB::rollBack();
            return back()->with('error', $e->getMessage());
        }
    }

    public function success($id)
    {
        $pesanan = Pesanan::with('detail.menu')->findOrFail($id);

        if ($pesanan->status_bayar != 1) {
            return redirect()->route('pesanan.payment', $id)
                ->with('error', 'Pembayaran belum selesai');
        }

        return view('pesanan.success', [
            'pesanan' => $pesanan,
            'qrCodeDataUri' => $pesanan->qr_code
        ]);
    }

    public function payment($id)
    {
        $pesanan = Pesanan::with('detail.menu')->findOrFail($id);

        $qrCodeDataUri = null;

        if ($pesanan->status_bayar == 1) {
            $qrCode = QrCode::create((string) $pesanan->idpesanan)
                ->setSize(300)
                ->setMargin(10);

            $writer = new PngWriter();
            $qrCodeDataUri = $writer->write($qrCode)->getDataUri();
        }

        return view('pesanan.payment', compact('pesanan', 'qrCodeDataUri'));
    }

    public function checkout(Request $request)
    {
        try {
            \Midtrans\Config::$serverKey = config('midtrans.server_key');
            \Midtrans\Config::$clientKey = config('midtrans.client_key');
            \Midtrans\Config::$isProduction = config('midtrans.is_production');
            \Midtrans\Config::$isSanitized = true;
            \Midtrans\Config::$is3ds = true;

            // 🔥 FIX UTAMA (JANGAN findOrFail langsung)
            $pesanan = Pesanan::where('idpesanan', $request->order_id)->first();

            if (!$pesanan) {
                return response()->json([
                    'error' => 'Pesanan tidak ditemukan: ' . $request->order_id
                ], 404);
            }

            // 🔥 MIDTRANS ORDER ID HARUS UNIK
            $midtransOrderId = 'ORDER-' . $pesanan->idpesanan . '-' . time();

            $params = [
                'transaction_details' => [
                    'order_id' => $midtransOrderId,
                    'gross_amount' => (int) $pesanan->total,
                ],
                'customer_details' => [
                    'first_name' => $pesanan->nama ?? 'Customer',
                ],
            ];

            $snapToken = \Midtrans\Snap::getSnapToken($params);

            // simpan order_id midtrans
            $pesanan->update([
                'order_id' => $midtransOrderId
            ]);

            return response()->json([
                'snap_token' => $snapToken
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'error' => 'Terjadi kesalahan: ' . $e->getMessage()
            ], 500);
        }
    }


    public function verifyStatus($id)
    {
        try {
            \Midtrans\Config::$serverKey = config('midtrans.server_key');
            \Midtrans\Config::$isProduction = config('midtrans.is_production');

            \Midtrans\Config::$curlOptions = [
                CURLOPT_SSL_VERIFYPEER => false,
                CURLOPT_SSL_VERIFYHOST => 0,
            ];

            $pesanan = Pesanan::findOrFail($id);

            if (!$pesanan->order_id) {
                return back()->with('error', 'Order ID belum tersedia');
            }

            // 🔥 retry 3x biar nunggu Midtrans update
            for ($i = 0; $i < 3; $i++) {

                $status = \Midtrans\Transaction::status($pesanan->order_id);

                Log::info('MIDTRANS STATUS', (array) $status);

                if (is_array($status)) {
                    $status = (object) $status;
                }

                if (in_array($status->transaction_status, ['settlement', 'capture'])) {

                    $pesanan->update([
                        'status_bayar' => 1,
                        'transaction_id' => $status->transaction_id ?? null,
                        'payment_type' => $status->payment_type ?? null,
                        'payment_details' => json_encode($status)
                    ]);

                    // 🔥 GENERATE QR SEKALI SAJA
                    $qrCode = QrCode::create((string) $pesanan->idpesanan)
                        ->setSize(300)
                        ->setMargin(10);

                    $writer = new PngWriter();
                    $qrImage = $writer->write($qrCode)->getDataUri();

                    $pesanan->update([
                        'status_bayar' => 1,
                        'transaction_id' => $status->transaction_id ?? null,
                        'payment_type' => $status->payment_type ?? null,
                        'payment_details' => json_encode($status),
                        'qr_code' => $qrImage
                    ]);

                    return redirect()->route('pesanan.success', $id);
                }

                // ⏳ tunggu 2 detik sebelum retry
                sleep(2);
            }

            // ❗ kalau masih pending
            return redirect()->route('pesanan.payment', $id)
                ->with('info', 'Pembayaran sedang diproses, silakan tunggu...');
        } catch (\Exception $e) {
            return back()->with('error', $e->getMessage());
        }
    }

    public function callback(Request $request)
    {
        Log::info('PESANAN CALLBACK HIT', [
            'method' => $request->method(),
            'order_id' => $request->order_id,
            'transaction_status' => $request->transaction_status,
            'status_code' => $request->status_code,
        ]);

        if ($request->isMethod('get')) {
            return response()->json([
                'status' => 'ok',
                'message' => 'Callback endpoint is active',
            ]);
        }

        $serverKey = config('midtrans.server_key') ?: env('MIDTRANS_SERVER_KEY');

        $hashed = hash(
            'sha512',
            $request->order_id .
                $request->status_code .
                $request->gross_amount .
                $serverKey
        );

        if ($hashed == $request->signature_key) {

            // Support alur kantin: order_id format SALE-{idpenjualan}-timestamp
            if (preg_match('/^SALE-(\d+)-/', (string) $request->order_id, $m)) {
                $idpenjualan = (int) $m[1];

                if (in_array($request->transaction_status, ['settlement', 'capture'])) {
                    $updateData = [
                        'status_bayar' => 1,
                        'transaction_id' => $request->transaction_id,
                        'payment_type' => $request->payment_type,
                        'payment_details' => json_encode($request->all()),
                    ];

                    if (\Schema::hasColumn('penjualan', 'order_id')) {
                        $updateData['order_id'] = $request->order_id;
                    }

                    DB::table('penjualan')
                        ->where('idpenjualan', $idpenjualan)
                        ->update($updateData);

                    Log::info('PESANAN CALLBACK UPDATED PENJUALAN', [
                        'idpenjualan' => $idpenjualan,
                        'order_id' => $request->order_id,
                    ]);
                }

                return response()->json(['status' => 'ok']);
            }

            $pesanan = Pesanan::where('order_id', $request->order_id)->first();

            if ($pesanan && in_array($request->transaction_status, ['settlement', 'capture'])) {

                $qrCode = \Endroid\QrCode\QrCode::create((string) $pesanan->idpesanan)
                    ->setSize(300)
                    ->setMargin(10);

                $writer = new \Endroid\QrCode\Writer\PngWriter();
                $qrImage = $writer->write($qrCode)->getDataUri();

                $pesanan->update([
                    'status_bayar' => 1,
                    'transaction_id' => $request->transaction_id,
                    'payment_type' => $request->payment_type,
                    'payment_details' => json_encode($request->all()),
                    'qr_code' => $qrImage
                ]);
            }
        } else {
            Log::warning('PESANAN CALLBACK INVALID SIGNATURE', [
                'order_id' => $request->order_id,
                'transaction_status' => $request->transaction_status,
            ]);
        }

        return response()->json(['status' => 'ok']);
    }
}
