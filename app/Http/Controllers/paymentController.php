<?php

namespace App\Http\Controllers\payment;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Endroid\QrCode\QrCode;
use Endroid\QrCode\Writer\PngWriter;


class paymentController extends Controller
{
    public function index(Request $request, $idpenjualan = null)
    {
        $idpenjualan = $idpenjualan ?? $request->query('idpenjualan') ?? session('idpenjualan');

        $penjualan = DB::table('penjualan')->where('idpenjualan', $idpenjualan)->first();
        $details = [];
        $qrCodeDataUri = null;

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
                $qrPayload = json_encode([
                    'type' => 'penjualan',
                    'idpenjualan' => (int) $penjualan->idpenjualan,
                    'order_id' => $penjualan->order_id,
                    'total' => (int) $penjualan->total,
                ]);

                if (class_exists(QrCode::class)) {
                    $qrCode = QrCode::create($qrPayload)
                        ->setSize(260)
                        ->setMargin(10);

                    $writer = new PngWriter();
                    $qrCodeDataUri = $writer->write($qrCode)->getDataUri();
                }
            }
        }

        return view('payment.index', compact('penjualan', 'details', 'qrCodeDataUri'));
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
        $serverKey = env('MIDTRANS_SERVER_KEY');
        $hashed = hash('sha512', $request->order_id . $request->status_code . $request->gross_amount . $serverKey);

        if ($hashed == $request->signature_key) {
            $penjualan = DB::table('penjualan')->where('order_id', $request->order_id)->first();
            if ($penjualan) {
                if ($request->transaction_status == 'settlement' || $request->transaction_status == 'capture') {
                    DB::table('penjualan')->where('idpenjualan', $penjualan->idpenjualan)->update([
                        'status_bayar' => 1,
                        'transaction_id' => $request->transaction_id,
                        'payment_type' => $request->payment_type,
                        'payment_details' => json_encode($request->all())
                    ]);
                }
            }
        }

        return response()->json(['status' => 'ok']);
    }
}