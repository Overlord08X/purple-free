<?php

namespace App\Http\Controllers\barang;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Barang;
use Illuminate\Support\Facades\DB;

class barangController extends Controller
{
    // Tampilkan halaman index
    public function index()
    {
        $barangs = Barang::orderBy('idbarang', 'asc')->get();
        return view('barang.index', compact('barangs'));
    }

    // Simpan data baru
    public function store(Request $request)
    {
        $request->validate([
            'nama_barang' => 'required|string',
            'harga_barang' => 'required|numeric',
        ]);

        // Trigger DB akan generate idbarang
        Barang::create([
            'nama_barang' => $request->nama_barang,
            'harga_barang' => $request->harga_barang,
            'created_at' => now(),
        ]);

        return response()->json(['success' => true, 'message' => 'Barang berhasil ditambahkan.']);
    }

    // Ambil data untuk edit
    public function edit($id)
    {
        $barang = Barang::findOrFail($id);
        return response()->json($barang);
    }

    // Update data
    public function update(Request $request, $id)
    {
        $request->validate([
            'nama_barang' => 'required|string',
            'harga_barang' => 'required|numeric',
        ]);

        $barang = Barang::findOrFail($id);
        $barang->update([
            'nama_barang' => $request->nama_barang,
            'harga_barang' => $request->harga_barang,
        ]);

        return response()->json(['success' => true, 'message' => 'Barang berhasil diupdate.']);
    }

    // Hapus data
    public function destroy($id)
    {
        $barang = Barang::findOrFail($id);
        $barang->delete();
        return response()->json(['success' => true, 'message' => 'Barang berhasil dihapus.']);
    }

    public function show($id)
    {
        $barang = DB::table('barang')
            ->where('idbarang', $id)
            ->first();

        return response()->json($barang);
    }
}
