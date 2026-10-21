<?php

use App\Http\Controllers\Admin\Sistem\PenggunaController;
use App\Http\Controllers\Admin\Sistem\ProfilController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Rute Admin — Sistem
|--------------------------------------------------------------------------
*/

// Akun panel admin (Pengaturan Sistem › Manajemen Pengguna): hanya Super Administrator.
Route::resource('pengguna', PenggunaController::class)->except('show')->middleware('can:kelola-sistem');
// Akun terhapus tampil sebagai Nonaktif; withTrashed() agar binding menemukannya.
Route::patch('pengguna/{pengguna}/aktifkan', [PenggunaController::class, 'aktifkan'])
    ->withTrashed()->middleware('can:kelola-sistem')->name('pengguna.aktifkan');

// Akun yang sedang login (menu pengguna › Profil Saya): semua peran.
Route::singleton('profil', ProfilController::class)->only(['edit', 'update']);
