<?php

use App\Http\Controllers\Admin\Kas\AkunKasController;
use App\Http\Controllers\Admin\Kas\KategoriTransaksiController;
use App\Http\Controllers\Admin\Kas\TransaksiKasController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Rute Admin — Kas Perusahaan
|--------------------------------------------------------------------------
*/

// Tanpa parameters(), Laravel menyingkat "akun-kas" menjadi {akun_ka}.
Route::resource('akun-kas', AkunKasController::class)
    ->except('show')
    ->parameters(['akun-kas' => 'akunKas']);

Route::resource('kategori-transaksi', KategoriTransaksiController::class)
    ->except('show')
    ->parameters(['kategori-transaksi' => 'kategoriTransaksi']);

Route::resource('transaksi-kas', TransaksiKasController::class)
    ->parameters(['transaksi-kas' => 'transaksiKas']);

// Bukti transaksi di disk privat hanya bisa diunduh lewat rute ber-auth ini.
Route::get('transaksi-kas/{transaksiKas}/bukti', [TransaksiKasController::class, 'bukti'])
    ->name('transaksi-kas.bukti');
