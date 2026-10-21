<?php

use App\Http\Controllers\Admin\Sistem\PenggunaController;
use App\Http\Controllers\Admin\Sistem\ProfilController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Rute Admin — Sistem
|--------------------------------------------------------------------------
*/

// Akun panel admin (Pengaturan Sistem › Manajemen Pengguna).
Route::resource('pengguna', PenggunaController::class)->except('show');

// Akun yang sedang login (menu pengguna › Profil Saya).
Route::singleton('profil', ProfilController::class)->only(['edit', 'update']);
