<?php

use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Auth;
use App\Http\Controllers\Auth\LoginController;
use App\Http\Controllers\dashboard\dashboardController;
use App\Http\Controllers\kategori\kategoriController;
use App\Http\Controllers\buku\bukuController;
use Barryvdh\DomPDF\Facade\Pdf;

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
    Route::get('/pdf/sertifikat', function () {
        $pdf = Pdf::loadView('pdf.sertifikat')->setPaper('a4', 'landscape');
        return $pdf->download('sertifikat.pdf');
    })->name('pdf.sertifikat');

    Route::get('/pdf/undangan', function () {
        $pdf = Pdf::loadView('pdf.undangan')->setPaper('a4', 'portrait');
        return $pdf->download('undangan.pdf');
    })->name('pdf.undangan');

});
