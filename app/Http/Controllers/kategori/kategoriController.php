<?php

namespace App\Http\Controllers\kategori;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Kategori;

class kategoriController extends Controller
{
    public function index()
    {
        $kategoris = Kategori::with('bukus')->get();
        return view('kategori.index', compact('kategoris'));
    }

        public function store(Request $request)
    {
        $request->validate([
            'nama_kategori' => 'required|string|max:255'
        ]);

        Kategori::create([
            'nama_kategori' => $request->nama_kategori
        ]);

        return redirect()->route('kategori.index')->with('success', 'Kategori berhasil ditambahkan');
    }

    public function destroy($id)
    {
        $kategori = Kategori::findOrFail($id);

        // Optional: hapus semua buku dalam kategori
        $kategori->bukus()->delete();

        $kategori->delete();

        return redirect()->route('kategori.index')->with('success', 'Kategori berhasil dihapus');
    }
}
