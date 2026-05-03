<?php

namespace App\Http\Controllers\payment;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Menu;
use App\Models\Pesanan;
use App\Models\Vendor;
use Illuminate\Support\Facades\Auth;

class vendorController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
    }

    public function dashboard()
    {
        $vendors = Vendor::orderBy('nama_vendor')->get();
        $menus = Menu::with('vendor')->orderBy('nama_menu')->get();
        $pesananLunas = Pesanan::where('status_bayar', 1)->with('detail.menu.vendor')->get();

        return view('vendor.dashboard', compact('vendors', 'menus', 'pesananLunas'));
    }

    public function storeMenu(Request $request)
    {
        $request->validate([
            'vendor_id' => 'required|exists:vendor,idvendor',
            'nama_menu' => 'required',
            'harga' => 'required|integer',
            'path_gambar' => 'nullable|image'
        ]);

        $path = null;
        if ($request->hasFile('path_gambar')) {
            $path = $request->file('path_gambar')->store('menus');
        }

        Menu::create([
            'nama_menu' => $request->nama_menu,
            'harga' => $request->harga,
            'path_gambar' => $path,
            'idvendor' => $request->vendor_id
        ]);

        return back()->with('success', 'Menu added');
    }

    public function destroyMenu($id)
    {
        Menu::findOrFail($id)->delete();
        return back()->with('success', 'Menu deleted');
    }
}