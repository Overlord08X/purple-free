<?php

namespace App\Http\Controllers\payment;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Vendor;
use App\Models\Menu;
use App\Models\Pesanan;
use App\Models\DetailPesanan;
use Illuminate\Support\Facades\DB;

class pesananController extends Controller
{
    public function index()
    {
        $vendors = Vendor::all();
        return view('pesanan.index', compact('vendors'));
    }

    public function getMenus($vendorId)
    {
        $menus = Menu::where('idvendor', $vendorId)->get();
        return response()->json($menus);
    }

    public function store(Request $request)
    {
        $request->validate([
            'vendor_id' => 'required|exists:vendors,idvendor',
            'items' => 'required|array',
            'items.*.menu_id' => 'required|exists:menu,idmenu',
            'items.*.quantity' => 'required|integer|min:1',
            'items.*.catatan' => 'nullable|string',
        ]);

        DB::beginTransaction();

        try {
            // Buat user guest jika belum ada
            $guestName = 'Guest_' . str_pad(Pesanan::count() + 1, 7, '0', STR_PAD_LEFT);

            // Hitung total
            $total = 0;
            foreach ($request->items as $item) {
                $menu = Menu::find($item['menu_id']);
                $total += $menu->harga * $item['quantity'];
            }

            // Buat pesanan
            $pesanan = Pesanan::create([
                'nama' => $guestName,
                'total' => $total,
                'status_bayar' => 0,
                'metode_bayar' => $request->metode_bayar ?? 1, // 1 VA, 2 QRIS
            ]);

            // Buat detail
            foreach ($request->items as $item) {
                $menu = Menu::find($item['menu_id']);
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

    public function payment($id)
    {
        $pesanan = Pesanan::with('detailPesanan.menu')->findOrFail($id);
        return view('pesanan.payment', compact('pesanan'));
    }
}