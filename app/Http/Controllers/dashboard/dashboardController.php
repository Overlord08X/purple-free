<?php

namespace App\Http\Controllers\dashboard;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Models\Buku;
use App\Models\Kategori;
use App\Models\Barang;

class dashboardController extends Controller
{
    public function index()
    {
        $totalBuku = Buku::count();
        $totalKategori = Kategori::count();
        $totalBarang = Barang::count();

        return view('dashboard.index', compact(
            'totalBuku',
            'totalKategori',
            'totalBarang'
        ));
    }

    public function cekKoneksi()
    {
        try {
            DB::connection()->getPdo();
            return "Koneksi ke database berhasil!";
        } catch (\Exception $e) {
            return "Koneksi ke database gagal: " . $e->getMessage();
        }
    }
}
