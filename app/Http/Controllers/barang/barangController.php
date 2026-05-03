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

    private function generateIdBarang(): string
    {
        $lastId = DB::table('barang')
            ->selectRaw("COALESCE(MAX(CAST(SUBSTRING(idbarang FROM 4) AS INTEGER)), 0) as last_number")
            ->value('last_number');

        return 'BRG' . str_pad(((int) $lastId + 1), 5, '0', STR_PAD_LEFT);
    }

    // Simpan data baru
    public function store(Request $request)
    {
        $request->validate([
            'nama_barang' => 'required|string',
            'harga_barang' => 'required|numeric',
        ]);

        Barang::create([
            'idbarang' => $this->generateIdBarang(),
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
