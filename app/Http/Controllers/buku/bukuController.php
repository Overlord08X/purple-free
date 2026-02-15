<?php

namespace App\Http\Controllers\buku;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Buku;

class bukuController extends Controller
{
    public function index()
    {
        $bukus = Buku::with('kategori')->get();
        return view('buku.index', compact('bukus'));
    }
}
