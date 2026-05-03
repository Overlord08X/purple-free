<?php

namespace App\Http\Controllers\project;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Models\Pesanan;
use App\Models\DetailPesanan;
use App\Models\Menu;
use App\Models\Vendor;
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
                'penjualan_detail.harga',
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
}
