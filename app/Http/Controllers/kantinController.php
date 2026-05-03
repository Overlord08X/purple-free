<?php

namespace App\Http\Controllers\project;

use App\Http\Controllers\Controller;
use App\Models\Menu;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class kantinController extends Controller
{
    public function index()
    {
        $barangs = DB::table('barang')->orderBy('nama_barang')->get();
        $menus = Menu::with('vendor')->orderBy('nama_menu')->get();
        return view('project.kantin', compact('barangs', 'menus'));
    }

    public function getBarang($kode)
    {
        $barang = DB::table('barang')
            ->where('idbarang', $kode)
            ->first();

        return response()->json($barang);
    }

    public function getItem($kode)
    {
        if (str_starts_with($kode, 'MNU')) {
            $menuId = (int) ltrim(substr($kode, 3), '0');
            $menu = Menu::with('vendor')->where('idmenu', $menuId)->first();

            if (!$menu) {
                return response()->json(null, 404);
            }

            return response()->json([
                'type' => 'menu',
                'id' => $menu->idmenu,
                'code' => 'MNU' . str_pad((string) $menu->idmenu, 5, '0', STR_PAD_LEFT),
                'name' => $menu->nama_menu,
                'price' => $menu->harga,
                'vendor_name' => $menu->vendor->nama_vendor ?? '-',
            ]);
        }

        $barang = DB::table('barang')
            ->where('idbarang', $kode)
            ->first();

        if (!$barang) {
            return response()->json(null, 404);
        }

        return response()->json([
            'type' => 'barang',
            'id' => $barang->idbarang,
            'code' => $barang->idbarang,
            'name' => $barang->nama_barang,
            'price' => $barang->harga_barang,
        ]);
    }

    public function simpan(Request $request)
    {
        $request->validate([
            'items' => 'required|array|min:1',
            'items.*.id' => 'required',
            'items.*.type' => 'required|in:barang,menu',
            'items.*.jumlah' => 'required|integer|min:1',
        ]);

        DB::beginTransaction();

        try {
            $total = 0;
            $preparedItems = [];

            foreach ($request->items as $item) {
                $type = $item['type'];
                $jumlah = (int) $item['jumlah'];
                $source = null;
                $harga = 0;
                $idbarang = null;
                $idmenu = null;
                $label = null;

                if ($type === 'menu') {
                    $idmenu = (int) $item['id'];
                    $source = Menu::find($idmenu);
                    if ($source) {
                        $harga = (int) $source->harga;
                        $label = $source->nama_menu;
                    }
                } else {
                    $idbarang = (string) $item['id'];
                    $source = DB::table('barang')->where('idbarang', $idbarang)->first();
                    if ($source) {
                        $harga = (int) $source->harga_barang;
                        $label = $source->nama_barang;
                    }
                }

                if (!$source) {
                    continue;
                }

                $subtotal = $harga * $jumlah;
                $total += $subtotal;

                $preparedItems[] = [
                    'type' => $type,
                    'idbarang' => $idbarang,
                    'idmenu' => $idmenu,
                    'jumlah' => $jumlah,
                    'subtotal' => $subtotal,
                    'label' => $label,
                ];
            }

            if (empty($preparedItems)) {
                return response()->json([
                    'error' => 'Tidak ada item yang valid untuk diproses.'
                ], 422);
            }

            // INSERT penjualan dan ambil id yang dibuat trigger
            $idpenjualan = DB::selectOne("
                INSERT INTO penjualan (created_at,total)
                VALUES (now(),?)
                RETURNING idpenjualan
            ", [$total])->idpenjualan;

            $nextDetailId = (int) DB::table('penjualan_detail')->max('idpenjualan_detail') + 1;

            // INSERT detail
            foreach ($preparedItems as $item) {
                DB::table('penjualan_detail')->insert([
                    'idpenjualan_detail' => $nextDetailId,
                    'idpenjualan' => $idpenjualan,
                    'idbarang' => $item['idbarang'],
                    'idmenu' => $item['idmenu'],
                    'item_type' => $item['type'],
                    'jumlah' => $item['jumlah'],
                    'subtotal' => $item['subtotal']
                ]);

                $nextDetailId++;
            }

            DB::commit();

            // Simpan idpenjualan ke session untuk payment
            session(['idpenjualan' => $idpenjualan, 'total' => $total]);

            return response()->json([
                'status' => 'success',
                'redirect' => route('payment.index', ['idpenjualan' => $idpenjualan])
            ]);
        } catch (\Exception $e) {

            DB::rollBack();

            return response()->json([
                'error' => $e->getMessage()
            ], 500);
        }
    }
}
