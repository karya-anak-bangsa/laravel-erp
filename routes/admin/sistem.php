<?php

use App\Http\Controllers\Admin\Sistem\PenggunaController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Rute Admin — Sistem
|--------------------------------------------------------------------------
*/

// Akun panel admin (Pengaturan Sistem › Manajemen Pengguna).
Route::resource('pengguna', PenggunaController::class)->except('show');
