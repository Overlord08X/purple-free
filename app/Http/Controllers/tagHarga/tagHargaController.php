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
        $barang = Barang::orderBy('nama_barang')->get();
        return view('tagHarga.index', compact('barang'));
    }

    public function cetak(Request $request)
    {
        $request->validate([
            'items' => 'required|array',
            'x'     => 'required|integer|min:1|max:5',
            'y'     => 'required|integer|min:1|max:8',
        ]);

        $x = (int) $request->x;
        $y = (int) $request->y;

        // Hitung posisi awal (1–40)
        $startIndex = (($y - 1) * 5) + $x;

        // Siapkan 40 slot label kosong
        $labels = array_fill(1, 40, null);
        $currentIndex = $startIndex;

        foreach ($request->items as $item) {

            // 🔥 AMAN dari undefined key
            if (!isset($item['id']) || !isset($item['qty'])) {
                continue;
            }

            $barang = Barang::where('idbarang', $item['id'])->first();
            if (!$barang) {
                continue;
            }

            $qty = (int) $item['qty'];

            if ($qty < 1) {
                $qty = 1;
            }

            for ($i = 0; $i < $qty; $i++) {

                if ($currentIndex > 40) {
                    break 2; // keluar dari dua loop
                }

                $labels[$currentIndex] = $barang;
                $currentIndex++;
            }
        }

        $pdf = Pdf::loadView('tagHarga.pdf', compact('labels'))
            ->setPaper('A4', 'portrait');

        return $pdf->stream('tag_harga.pdf');
    }
}
