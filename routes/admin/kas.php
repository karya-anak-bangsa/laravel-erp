<?php

use App\Http\Controllers\Admin\Kas\AkunKasController;
use App\Http\Controllers\Admin\Kas\KategoriTransaksiController;
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
