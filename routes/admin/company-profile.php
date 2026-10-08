<?php

use App\Http\Controllers\Admin\CompanyProfile\ArtikelController;
use App\Http\Controllers\Admin\CompanyProfile\FaqController;
use App\Http\Controllers\Admin\CompanyProfile\HeroController;
use App\Http\Controllers\Admin\CompanyProfile\IdentitasController;
use App\Http\Controllers\Admin\CompanyProfile\KategoriArtikelController;
use App\Http\Controllers\Admin\CompanyProfile\KontakKamiController;
use App\Http\Controllers\Admin\CompanyProfile\LayananController;
use App\Http\Controllers\Admin\CompanyProfile\PortofolioController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Rute Admin — Company Profile
|--------------------------------------------------------------------------
*/

// Singleton: identitas selalu satu baris, jadi tanpa index/create/destroy.
Route::singleton('identitas', IdentitasController::class)->only(['edit', 'update']);

Route::resource('hero', HeroController::class)->except('show');

Route::resource('layanan', LayananController::class)->except('show');

Route::resource('portofolio', PortofolioController::class)->except('show');

Route::resource('artikel', ArtikelController::class)->except('show');

Route::resource('kategori-artikel', KategoriArtikelController::class)->except('show');

Route::resource('faq', FaqController::class)->except('show');

// Pesan masuk dari form kontak publik: admin hanya membaca, menandai, dan menghapus.
Route::resource('kontak-kami', KontakKamiController::class)->only(['index', 'destroy']);
Route::patch('kontak-kami/{kontak_kami}/status-baca', [KontakKamiController::class, 'statusBaca'])
    ->name('kontak-kami.status-baca');
