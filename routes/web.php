<?php

use App\Http\Controllers\Auth\LoginController;
use App\Http\Controllers\Web\CompanyProfile\BerandaController;
use App\Http\Controllers\Web\CompanyProfile\KontakKamiController;
use Illuminate\Routing\RedirectController;
use Illuminate\Support\Facades\Route;

// Frontend publik: di luar grup guest agar admin yang sedang login tetap bisa melihat situs.
Route::get('/', BerandaController::class)->name('beranda');

Route::post('kontak', [KontakKamiController::class, 'store'])
    ->middleware('throttle:kontak')
    ->name('kontak-kami.store');
// Halaman kontak tersendiri belum ada; GET (mis. muat ulang setelah kirim) diarahkan ke formulir di beranda.
// Bukan Route::redirect(): itu mendaftarkan semua method dan akan menimpa rute POST di atas.
Route::get('kontak', RedirectController::class)
    ->defaults('destination', '/#kontak')
    ->defaults('status', 302);

Route::middleware('guest')->group(function () {
    Route::get('login', [LoginController::class, 'create'])->name('login');
    Route::post('login', [LoginController::class, 'store'])->name('login.store');
});

Route::post('logout', [LoginController::class, 'destroy'])
    ->middleware('auth')
    ->name('logout');
