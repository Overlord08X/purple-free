<?php

namespace App\Http\Controllers\payment;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Menu;
use App\Models\Pesanan;
use Illuminate\Support\Facades\Auth;

class vendorController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
    }

    public function dashboard()
    {
        // Asumsi vendor_id disimpan di user atau cari cara lain
        // Untuk sementara, asumsikan user id = vendor id
        $vendorId = Auth::id();

        $menus = Menu::where('idvendor', $vendorId)->get();
        $pesananLunas = Pesanan::whereHas('detail', function($q) use ($vendorId) {
            $q->whereHas('menu', function($qq) use ($vendorId) {
                $qq->where('idvendor', $vendorId);
            });
        })->where('status_bayar', 1)->with('detail.menu')->get();

        return view('vendor.dashboard', compact('menus', 'pesananLunas'));
    }

    public function storeMenu(Request $request)
    {
        $request->validate([
            'nama_menu' => 'required',
            'harga' => 'required|integer',
            'path_gambar' => 'nullable|image'
        ]);

        $vendorId = Auth::id();

        $path = null;
        if ($request->hasFile('path_gambar')) {
            $path = $request->file('path_gambar')->store('menus');
        }

        Menu::create([
            'nama_menu' => $request->nama_menu,
            'harga' => $request->harga,
            'path_gambar' => $path,
            'idvendor' => $vendorId
        ]);

        return back()->with('success', 'Menu added');
    }

    public function destroyMenu($id)
    {
        Menu::findOrFail($id)->delete();
        return back()->with('success', 'Menu deleted');
    }
}