<?php

namespace App\Http\Controllers\qrcode;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use SimpleSoftwareIO\QrCode\Facades\QrCode;
use Illuminate\Support\Facades\Storage;
use App\Models\Customer;

class qrcodeController extends Controller
{
    public function index()
    {
        return view('qrcode.index');
    }

    // =========================
    // SIMPAN + GENERATE QR
    // =========================
    public function store(Request $request)
    {
        $request->validate([
            'nama' => 'required'
        ]);

        $customer = Customer::create([
            'nama' => $request->nama
        ]);

        // isi QR
        $qrText = "CUSTOMER-" . $customer->id;

        // nama file
        $fileName = 'qr_' . $customer->id . '.png';

        // generate QR
        Storage::disk('public')->put(
            'qrcode/' . $fileName,
            QrCode::format('png')->size(300)->generate($qrText)
        );

        // simpan path
        $customer->update([
            'qr_code_path' => 'qrcode/' . $fileName
        ]);

        return redirect()->route('qrcode.show', $customer->id);
    }

    // =========================
    // TAMPILKAN DATA
    // =========================
    public function show($id)
    {
        $customer = Customer::findOrFail($id);

        return view('customer_detail', compact('customer'));
    }
}