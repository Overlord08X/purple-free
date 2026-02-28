<?php

use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Auth;
use App\Http\Controllers\Auth\LoginController;
use App\Http\Controllers\dashboard\dashboardController;
use App\Http\Controllers\kategori\kategoriController;
use App\Http\Controllers\buku\bukuController;
use App\Http\Controllers\pdf\pdfController;
use App\Http\Controllers\tagHarga\tagHargaController;

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
});
