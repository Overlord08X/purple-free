<?php

namespace App\Http\Controllers\tagHarga;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Barang;
use Barryvdh\DomPDF\Facade\Pdf;

class tagHargaController extends Controller
{
    public function index()
    {
        $barang = Barang::all();
        return view('tagHarga.index', compact('barang'));
    }

    public function cetak(Request $request)
    {
        $request->validate([
            'id' => 'required|array',
            'x' => 'required|integer|min:1|max:5',
            'y' => 'required|integer|min:1|max:8',
        ]);

        $barang = Barang::whereIn('idbarang', $request->id)->get();

        $startX = $request->x;
        $startY = $request->y;

        $startIndex = ($startY - 1) * 5 + $startX;

        $labels = array_fill(1, 40, null);

        $i = $startIndex;

        foreach ($barang as $b) {
            if ($i > 40) break;
            $labels[$i] = $b;
            $i++;
        }

        $pdf = PDF::loadView('tagHarga.pdf', compact('labels'));
        return $pdf->stream('tag_harga.pdf')
            ->withHeaders([
                'X-Success-Message' => 'PDF berhasil dibuat'
            ]);
    }
}
