<?php

namespace App\Http\Controllers;

use App\Models\Customer;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class CustomerController extends Controller
{
    public function indexBlob()
    {
        return view('customer.customer_blob');
    }

    public function storeBlob(Request $request)
    {
        $request->validate([
            'nama' => 'required|string|max:255',
            'foto' => 'required|string'
        ]);

        try {
            $base64Image = $request->input('foto');
            
            if (strpos($base64Image, 'data:image/png;base64,') === 0) {
                $base64Image = substr($base64Image, 22);
            }

            Customer::create([
                'nama' => $request->input('nama'),
                'foto_blob' => $base64Image
            ]);

            return redirect('/customer/blob')->with('success', 'Customer berhasil disimpan dengan BLOB');
        } catch (\Exception $e) {
            return back()->with('error', 'Terjadi kesalahan: ' . $e->getMessage());
        }
    }

    public function indexFile()
    {
        return view('customer.customer_file');
    }

    public function storeFile(Request $request)
    {
        $request->validate([
            'nama' => 'required|string|max:255',
            'foto' => 'required|string'
        ]);

        try {
            $base64Image = $request->input('foto');
            
            if (strpos($base64Image, 'data:image/png;base64,') === 0) {
                $base64Image = substr($base64Image, 22);
            }

            $imageData = base64_decode($base64Image, true);

            if ($imageData === false) {
                return back()->with('error', 'Gambar tidak valid');
            }

            $filename = 'customer_' . time() . '_' . uniqid() . '.png';
            Storage::disk('public')->put('customers/' . $filename, $imageData);

            Customer::create([
                'nama' => $request->input('nama'),
                'foto_path' => 'customers/' . $filename
            ]);

            return redirect('/customer/file')->with('success', 'Customer berhasil disimpan dengan FILE');
        } catch (\Exception $e) {
            return back()->with('error', 'Terjadi kesalahan: ' . $e->getMessage());
        }
    }

    public function showBlob($id)
    {
        $customer = Customer::findOrFail($id);

        if (!$customer->foto_blob) {
            abort(404);
        }

        $imageData = base64_decode($customer->foto_blob);

        return response($imageData)
            ->header('Content-Type', 'image/png');
    }
}
