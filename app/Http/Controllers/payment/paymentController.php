<?php

namespace App\Http\Controllers\payment;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\Log;
use Endroid\QrCode\QrCode;
use Endroid\QrCode\Writer\PngWriter;


class paymentController extends Controller
{
    public function index(Request $request, $idpenjualan = null)
    {
        $idpenjualan = $idpenjualan ?? $request->query('idpenjualan') ?? session('idpenjualan');

        $transactions = DB::table('penjualan')
            ->orderByDesc('idpenjualan')
            ->paginate(15);

        $penjualan = DB::table('penjualan')->where('idpenjualan', $idpenjualan)->first();
        $details = [];
        $qrCodeDataUri = null;

        // Fallback sync: if callback missed, verify paid status directly to Midtrans.
        if ($penjualan && $request->query('paid') == '1' && (int) $penjualan->status_bayar !== 1 && !empty($penjualan->order_id)) {
            try {
                \Midtrans\Config::$serverKey = config('midtrans.server_key') ?: env('MIDTRANS_SERVER_KEY');
                \Midtrans\Config::$isProduction = false;
                \Midtrans\Config::$curlOptions = [
                    CURLOPT_SSL_VERIFYPEER => false,
                    CURLOPT_SSL_VERIFYHOST => 0,
                ];

                $status = \Midtrans\Transaction::status($penjualan->order_id);
                if (is_array($status)) {
                    $status = (object) $status;
                }

                if (in_array($status->transaction_status ?? null, ['settlement', 'capture'])) {
                    DB::table('penjualan')
                        ->where('idpenjualan', $penjualan->idpenjualan)
                        ->update([
                            'status_bayar' => 1,
                            'transaction_id' => $status->transaction_id ?? $penjualan->transaction_id,
                            'payment_type' => $status->payment_type ?? $penjualan->payment_type,
                            'payment_details' => json_encode($status),
                        ]);

                    $penjualan = DB::table('penjualan')->where('idpenjualan', $idpenjualan)->first();
                }
            } catch (\Throwable $e) {
                // Keep page usable even if status sync fails.
            }
        }

        if ($penjualan) {
            $details = DB::table('penjualan_detail')
                ->leftJoin('barang', 'penjualan_detail.idbarang', '=', 'barang.idbarang')
                ->leftJoin('menu', 'penjualan_detail.idmenu', '=', 'menu.idmenu')
                ->leftJoin('vendor', 'menu.idvendor', '=', 'vendor.idvendor')
                ->where('penjualan_detail.idpenjualan', $idpenjualan)
                ->select(
                    'penjualan_detail.*',
                    DB::raw('COALESCE(barang.nama_barang, menu.nama_menu) as nama_item'),
                    DB::raw('COALESCE(barang.harga_barang, menu.harga) as harga'),
                    'menu.nama_menu',
                    'barang.nama_barang',
                    'vendor.nama_vendor as vendor_name'
                )
                ->get();

            // Tampilkan QR setelah user selesai transaksi di halaman ini (?paid=1)
            if ($request->query('paid') == '1') {
                // Generate QR hanya dengan idpenjualan untuk vendor scanner
                if (class_exists(QrCode::class)) {
                    $qrCode = QrCode::create((string) $penjualan->idpenjualan)
                        ->setSize(260)
                        ->setMargin(10);

                    $writer = new PngWriter();
                    $qrCodeDataUri = $writer->write($qrCode)->getDataUri();
                }
            }
        }

        return view('payment.index', compact('penjualan', 'details', 'qrCodeDataUri', 'transactions'));
    }

    public function edit($idpenjualan)
    {
        $penjualan = DB::table('penjualan')->where('idpenjualan', $idpenjualan)->first();

        abort_if(!$penjualan, 404, 'Transaksi tidak ditemukan');

        return view('payment.edit', compact('penjualan'));
    }

    public function update(Request $request, $idpenjualan)
    {
        $request->validate([
            'total' => 'required|integer|min:0',
            'status_bayar' => 'required|in:0,1',
            'payment_type' => 'nullable|string|max:255',
        ]);

        $penjualan = DB::table('penjualan')->where('idpenjualan', $idpenjualan)->first();
        abort_if(!$penjualan, 404, 'Transaksi tidak ditemukan');

        DB::table('penjualan')
            ->where('idpenjualan', $idpenjualan)
            ->update([
                'total' => (int) $request->total,
                'status_bayar' => (int) $request->status_bayar,
                'payment_type' => $request->payment_type,
            ]);

        return redirect()->route('payment.index', ['id' => $idpenjualan])
            ->with('success', 'Transaksi berhasil diperbarui.');
    }

    public function destroy($idpenjualan)
    {
        $penjualan = DB::table('penjualan')->where('idpenjualan', $idpenjualan)->first();
        abort_if(!$penjualan, 404, 'Transaksi tidak ditemukan');

        DB::table('penjualan')->where('idpenjualan', $idpenjualan)->delete();

        return redirect()->route('payment.index')
            ->with('success', 'Transaksi berhasil dihapus.');
    }

    public function checkout(Request $request)
    {
        \Midtrans\Config::$serverKey = env('MIDTRANS_SERVER_KEY');
        \Midtrans\Config::$isProduction = false;
        \Midtrans\Config::$isSanitized = true;
        \Midtrans\Config::$is3ds = true;

        $penjualan = DB::table('penjualan')->where('idpenjualan', $request->order_id)->first();

        if (!$penjualan) {
            return response()->json([
                'error' => 'Data penjualan tidak ditemukan.'
            ], 404);
        }

        $midtransOrderId = 'SALE-' . $penjualan->idpenjualan . '-' . time();

        $params = [
            'transaction_details' => [
                'order_id' => $midtransOrderId,
                'gross_amount' => (int) $penjualan->total,
            ],
            'customer_details' => [
                'first_name' => 'Customer',
            ],
            'enabled_payments' => ['qris', 'gopay', 'bank_transfer'],
        ];

        // Simpan order_id jika kolom sudah tersedia di database.
        if (Schema::hasColumn('penjualan', 'order_id')) {
            DB::table('penjualan')
                ->where('idpenjualan', $penjualan->idpenjualan)
                ->update(['order_id' => $midtransOrderId]);
        }

        try {
            $snapToken = \Midtrans\Snap::getSnapToken($params);
        } catch (\Throwable $e) {
            return response()->json([
                'error' => $e->getMessage()
            ], 500);
        }

        return response()->json([
            'snap_token' => $snapToken
        ]);
    }

    public function callback(Request $request)
    {
        Log::info('MIDTRANS PENJUALAN CALLBACK HIT', [
            'method' => $request->method(),
            'order_id' => $request->order_id,
            'transaction_status' => $request->transaction_status,
            'status_code' => $request->status_code,
        ]);

        if ($request->isMethod('get')) {
            return response()->json([
                'status' => 'ok',
                'message' => 'Midtrans callback endpoint is active',
            ]);
        }

        $serverKey = config('midtrans.server_key') ?: env('MIDTRANS_SERVER_KEY');
        $hashed = hash('sha512', $request->order_id . $request->status_code . $request->gross_amount . $serverKey);

        if ($hashed == $request->signature_key) {
            // Try to find penjualan by stored order_id first
            $penjualan = DB::table('penjualan')->where('order_id', $request->order_id)->first();

            // If not found, try to parse idpenjualan from order_id (format: SALE-{idpenjualan}-timestamp)
            if (!$penjualan && preg_match('/SALE-(\d+)-/', $request->order_id, $m)) {
                $idpenjualanFromOrder = (int) $m[1];
                $penjualan = DB::table('penjualan')->where('idpenjualan', $idpenjualanFromOrder)->first();
            }

            if ($penjualan) {
                if ($request->transaction_status == 'settlement' || $request->transaction_status == 'capture') {
                    // Update penjualan; if order_id column exists it may be null, so also set it
                    $updateData = [
                        'status_bayar' => 1,
                        'transaction_id' => $request->transaction_id,
                        'payment_type' => $request->payment_type,
                        'payment_details' => json_encode($request->all())
                    ];

                    // Try to save order_id if column exists
                    try {
                        if (\Schema::hasColumn('penjualan', 'order_id')) {
                            $updateData['order_id'] = $request->order_id;
                        }
                    } catch (\Throwable $e) {
                        // ignore schema inspection errors
                    }

                    DB::table('penjualan')->where('idpenjualan', $penjualan->idpenjualan)->update($updateData);
                }
            }
        } else {
            Log::warning('MIDTRANS PENJUALAN INVALID SIGNATURE', [
                'order_id' => $request->order_id,
                'transaction_status' => $request->transaction_status,
            ]);
        }

        return response()->json(['status' => 'ok']);
    }
}