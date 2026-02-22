<?php

namespace App\Http\Controllers\buku;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Buku;
use App\Models\Kategori;

class bukuController extends Controller
{
    public function index()
    {
        $bukus = Buku::with('kategori')->get();
        $kategoris = Kategori::all();

        return view('buku.index', compact('bukus', 'kategoris'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'idkategori' => 'required|exists:kategoris,idkategori',
            'kode'       => 'required|string|max:50',
            'judul'      => 'required|string|max:255',
            'pengarang'  => 'required|string|max:255',
        ]);

        Buku::create([
            'idkategori' => $request->idkategori,
            'kode'       => $request->kode,
            'judul'      => $request->judul,
            'pengarang'  => $request->pengarang,
        ]);

        return redirect()->route('buku.index')->with('success', 'Buku berhasil ditambahkan');
    }

    public function destroy($id)
    {
        $buku = Buku::findOrFail($id);
        $buku->delete();

        return redirect()->route('buku.index')->with('success', 'Buku berhasil dihapus');
    }
}
