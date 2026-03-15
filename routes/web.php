<?php

use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Auth;
use App\Http\Controllers\Auth\LoginController;
use App\Http\Controllers\dashboard\dashboardController;
use App\Http\Controllers\kategori\kategoriController;
use App\Http\Controllers\buku\bukuController;
use App\Http\Controllers\pdf\pdfController;
use App\Http\Controllers\tagHarga\tagHargaController;
use App\Http\Controllers\project\projectController;
use App\Http\Controllers\project\wilayahController;
use App\Http\Controllers\project\posController;
use App\Http\Controllers\barang\barangController;

Route::redirect('/', '/login');

Auth::routes();

// Google Login
Route::get('/auth/google', [LoginController::class, 'redirectToGoogle'])->name('google.login');
Route::get('/auth/google/callback', [LoginController::class, 'handleGoogle'])->name('google.callback');

// OTP Routes
Route::get('/otp', [LoginController::class, 'showOtpForm'])->name('otp.form');
Route::post('/otp/verify', [LoginController::class, 'verifyOtp'])->name('otp.verify');

// Protected Routes
Route::middleware(['auth', 'check.session'])->group(function () {

    Route::get('/dashboard', [dashboardController::class, 'index'])->name('dashboard.index');
    Route::get('/kategori', [kategoriController::class, 'index'])->name('kategori.index');
    Route::post('/kategori/store', [kategoriController::class, 'store'])->name('kategori.store');
    Route::delete('/kategori/{id}', [kategoriController::class, 'destroy'])->name('kategori.destroy');
    Route::get('/buku', [bukuController::class, 'index'])->name('buku.index');
    Route::post('/buku/store', [bukuController::class, 'store'])->name('buku.store');
    Route::delete('/buku/{id}', [bukuController::class, 'destroy'])->name('buku.destroy');

    // PDF Routes
    Route::get('pdf/landscape', [pdfController::class, 'sertifikat'])->name('pdf.sertifikat');
    Route::get('pdf/portrait', [pdfController::class, 'undangan'])->name('pdf.undangan');

    // Tag Harga Routes
    Route::get('/tagHarga', [TagHargaController::class, 'index'])->name('tagHarga.index');
    Route::post('/tagHarga/cetak', [TagHargaController::class, 'cetak'])->name('tagharga.cetak');


    Route::resource('barang', BarangController::class);

    Route::get('/project', [projectController::class, 'index'])->name('project.index');
    Route::get('/kota', [projectController::class, 'kota'])->name('project.kota');

    Route::get('/wilayah', [wilayahController::class, 'index'])->name('project.wilayah');

    Route::get('/provinsi', [wilayahController::class, 'provinsi']);
    Route::get('/kota/{id}', [wilayahController::class, 'kota']);
    Route::get('/kecamatan/{id}', [wilayahController::class, 'kecamatan']);
    Route::get('/kelurahan/{id}', [wilayahController::class, 'kelurahan']);

    Route::get('/pos', [posController::class, 'index'])->name('project.pos');

    Route::get('/barang/{kode}', function ($kode) {
        $barang = \App\Models\Barang::where('idbarang', $kode)->first();
        return response()->json($barang); // penting: JSON
    });

    Route::post('/transaksi', [posController::class, 'simpan']);
});
