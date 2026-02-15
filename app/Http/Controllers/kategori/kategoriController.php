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
}
