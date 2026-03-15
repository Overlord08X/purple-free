<?php

namespace App\Http\Controllers\project;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class posController extends Controller
{
    public function index()
    {
        return view('project.pos');
    }

    public function getBarang($kode)
    {
        $barang = DB::table('barang')
            ->where('idbarang', $kode)
            ->first();

        return response()->json($barang);
    }

    public function simpan(Request $request)
    {

        DB::beginTransaction();

        try {

            $total = 0;

            foreach ($request->items as $item) {
                $total += $item['subtotal'];
            }

            // INSERT penjualan dan ambil id yang dibuat trigger
            $idpenjualan = DB::selectOne("
                INSERT INTO penjualan (created_at,total)
                VALUES (now(),?)
                RETURNING idpenjualan
            ", [$total])->idpenjualan;

            // INSERT detail
            foreach ($request->items as $item) {

                DB::table('penjualan_detail')->insert([
                    'idpenjualan' => $idpenjualan,
                    'idbarang' => $item['kode'],
                    'jumlah' => $item['jumlah'],
                    'subtotal' => $item['subtotal']
                ]);
            }

            DB::commit();

            return response()->json([
                'status' => 'success'
            ]);
        } catch (\Exception $e) {

            DB::rollBack();

            return response()->json([
                'error' => $e->getMessage()
            ], 500);
        }
    }
}
