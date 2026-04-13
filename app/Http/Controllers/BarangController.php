<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Barang;
use Barryvdh\DomPDF\Facade\Pdf;
use Picqer\Barcode\BarcodeGeneratorPNG;

class BarangController extends Controller
{
    public function index()
    {
        $barangs = Barang::all();
        return view('barang.index', compact('barangs'));
    }

    public function cetakPDF($id)
    {
        $barang = Barang::findOrFail($id);

        $generator = new BarcodeGeneratorPNG();
        $barcode = base64_encode($generator->getBarcode($barang->idbarang, $generator::TYPE_CODE_128));

        $pdf = Pdf::loadView('barang.pdf', compact('barang', 'barcode'))
            ->setPaper('A4', 'portrait');

        return $pdf->stream('tag_harga_' . $barang->idbarang . '.pdf');
    }
}