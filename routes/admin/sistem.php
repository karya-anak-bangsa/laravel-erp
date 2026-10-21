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

// Akun yang sedang login (menu pengguna › Profil Saya): semua peran.
Route::singleton('profil', ProfilController::class)->only(['edit', 'update']);
