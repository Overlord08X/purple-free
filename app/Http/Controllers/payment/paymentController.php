<?php

namespace App\Http\Controllers\payment;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;


class paymentController extends Controller
{
    public function index(Request $request, $idpenjualan = null)
    {
        $idpenjualan = $idpenjualan ?? $request->query('idpenjualan') ?? session('idpenjualan');

        $penjualan = DB::table('penjualan')->where('idpenjualan', $idpenjualan)->first();
        $details = [];

        if ($penjualan) {
            $details = DB::table('penjualan_detail')
                ->join('barang', 'penjualan_detail.idbarang', '=', 'barang.idbarang')
                ->where('penjualan_detail.idpenjualan', $idpenjualan)
                ->select('penjualan_detail.*', 'barang.nama_barang', 'barang.harga_barang as harga')
                ->get();
        }

        return view('payment.index', compact('penjualan', 'details'));
    }

    public function checkout(Request $request)
    {
        \Midtrans\Config::$serverKey = env('MIDTRANS_SERVER_KEY');
        \Midtrans\Config::$isProduction = false;

        $penjualan = DB::table('penjualan')->where('idpenjualan', $request->order_id)->first();

        // Config ke midtrans
        $params = [
            'transaction_details' => [
                'order_id' => 'SALE-' . $penjualan->idpenjualan,
                'gross_amount' => $penjualan->total,
            ],
            'customer_details' => [
                'first_name' => 'Customer',
            ],
            'payment_type' => 'bank_transfer', // default VA
        ];

        // Ambil token
        $snapToken = \Midtrans\Snap::getSnapToken($params);

        // Simpan order_id jika kolom sudah tersedia di database.
        if (Schema::hasColumn('penjualan', 'order_id')) {
            DB::table('penjualan')
                ->where('idpenjualan', $penjualan->idpenjualan)
                ->update(['order_id' => 'SALE-' . $penjualan->idpenjualan]);
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