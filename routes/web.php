<?php

use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Auth;
use App\Http\Controllers\Auth\LoginController;
use App\Http\Controllers\dashboard\dashboardController;
use App\Http\Controllers\kategori\kategoriController;
use App\Http\Controllers\buku\bukuController;
use App\Http\Controllers\pdf\pdfController;
use App\Http\Controllers\tagHarga\tagHargaController as TagHargaController;
use App\Http\Controllers\project\projectController;
use App\Http\Controllers\project\wilayahController;
use App\Http\Controllers\project\posController;
use App\Http\Controllers\project\kantinController;
use App\Http\Controllers\payment\pesananController;
use App\Http\Controllers\payment\vendorController;
use App\Http\Controllers\barang\barangController as BarangController;
use App\Http\Controllers\payment\paymentController;
use App\Http\Controllers\CustomerController;
use App\Http\Controllers\qrcode\qrcodeController;

Route::redirect('/', '/dashboard');

Auth::routes();

// Google Login
Route::get('/auth/google', [LoginController::class, 'redirectToGoogle'])->name('google.login');
Route::get('/auth/google/callback', [LoginController::class, 'handleGoogle'])->name('google.callback');

// OTP Routes
Route::get('/otp', [LoginController::class, 'showOtpForm'])->name('otp.form');
Route::post('/otp/verify', [LoginController::class, 'verifyOtp'])->name('otp.verify');

// Pesanan Routes (public)
Route::prefix('pesanan')->group(function () {
    Route::get('/', [pesananController::class, 'index'])->name('pesanan.index');
    Route::get('/menus/{vendorId}', [pesananController::class, 'getMenus']);
    Route::post('/store', [pesananController::class, 'store']);
    Route::get('/payment/{id}', [pesananController::class, 'payment'])->name('pesanan.payment');
    Route::get('/success/{id}', [pesananController::class, 'success'])->name('pesanan.success');

    Route::post('/checkout', [pesananController::class, 'checkout'])->name('pesanan.checkout');
    Route::post('/callback', [pesananController::class, 'callback']);
});

// Customer Routes (public)
Route::get('/customer/blob', [CustomerController::class, 'indexBlob'])->name('customer.blob');
Route::post('/customer/blob', [CustomerController::class, 'storeBlob']);
Route::get('/customer/file', [CustomerController::class, 'indexFile'])->name('customer.file');
Route::post('/customer/file', [CustomerController::class, 'storeFile']);
Route::get('/customer/blob/{id}', [CustomerController::class, 'showBlob'])->name('customer.show.blob');

// Protected Routes
Route::middleware(['auth', 'check.session'])->group(function () {

    Route::get('/dashboard', [dashboardController::class, 'index'])->name('dashboard.index');
    Route::get('/vendor/dashboard', [vendorController::class, 'dashboard'])->name('vendor.dashboard');
    Route::post('/vendor/menu', [vendorController::class, 'storeMenu'])->name('vendor.menu.store');
    Route::delete('/vendor/menu/{id}', [vendorController::class, 'destroyMenu'])->name('vendor.menu.destroy');
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
    Route::get('/kantin', [kantinController::class, 'index'])->name('project.kantin');

    Route::get('/barang/{kode}', function ($kode) {
        $barang = \App\Models\Barang::where('idbarang', $kode)->first();
        return response()->json($barang); // penting: JSON
    });

    Route::post('/transaksi', [kantinController::class, 'simpan']);

    Route::get('/barang', [BarangController::class, 'index'])->name('barang.index');
    Route::get('/barang/cetak/{id}', [BarangController::class, 'cetakPDF'])->name('barang.cetak');

    Route::prefix('payment')->group(function () {
        Route::get('/{id?}', [paymentController::class, 'index'])->name('payment.index');
        Route::post('/checkout', [paymentController::class, 'checkout'])->name('payment.checkout');
        Route::post('/callback', [paymentController::class, 'callback']);
    });

    Route::get('/qrcode', [qrcodeController::class, 'index'])->name('qrcode.index');
    Route::get('/qrcode/{id}', [qrcodeController::class, 'show'])->name('qrcode.show');
    Route::post('/qrcode', [qrcodeController::class, 'store'])->name('qrcode.store');
});
