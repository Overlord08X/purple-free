<?php

use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Auth;
use App\Http\Controllers\dashboard\dashboardController;
use App\Http\Controllers\kategori\kategoriController;
use App\Http\Controllers\buku\bukuController;

Route::get('/', function () {
    return view('welcome');
});

Route::get('/cek-koneksi', [dashboardController::class, 'cekKoneksi'])->name('cek-koneksi');

Route::get('/home', [App\Http\Controllers\HomeController::class, 'index'])->name('home');

Auth::routes();

Route::middleware(['auth', 'check.session'])->group(function () {
    Route::get('/dashboard', [dashboardController::class, 'index'])->name('dashboard.index');
    Route::get('/kategori', [kategoriController::class, 'index'])->name('kategori.index');
    Route::get('/buku', [bukuController::class, 'index'])->name('buku.index');
});