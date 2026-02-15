<?php

namespace App\Http\Controllers\dashboard;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class dashboardController extends Controller
{
    public function index()
    {
        return view('dashboard.index');
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
