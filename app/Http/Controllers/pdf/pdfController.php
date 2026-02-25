<?php

namespace App\Http\Controllers\pdf;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Barryvdh\DomPDF\Facade\Pdf;

class pdfController extends Controller
{
public function sertifikat()
    {
        $pdf = Pdf::loadView('pdf.landscape')->setPaper('a4', 'landscape');
        // return $pdf->stream('sertifikat.pdf');
        return $pdf->download('sertifikat.pdf');
    }

    public function undangan()
    {
        $pdf = Pdf::loadView('pdf.portrait')->setPaper('a4', 'portrait');
        return $pdf->stream('undangan.pdf');
    }
}
