<?php

use App\Http\Controllers\Admin\CompanyProfile\ArtikelController;
use App\Http\Controllers\Admin\CompanyProfile\FaqController;
use App\Http\Controllers\Admin\CompanyProfile\HeroController;
use App\Http\Controllers\Admin\CompanyProfile\IdentitasController;
use App\Http\Controllers\Admin\CompanyProfile\KategoriArtikelController;
use App\Http\Controllers\Admin\CompanyProfile\KategoriPortofolioController;
use App\Http\Controllers\Admin\CompanyProfile\KategoriTutorialController;
use App\Http\Controllers\Admin\CompanyProfile\LayananController;
use App\Http\Controllers\Admin\CompanyProfile\MateriTutorialController;
use App\Http\Controllers\Admin\CompanyProfile\ModulWebsiteController;
use App\Http\Controllers\Admin\CompanyProfile\PortofolioController;
use App\Http\Controllers\Admin\CompanyProfile\TemplateController;
use App\Http\Controllers\Admin\CompanyProfile\TutorialController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Rute Admin — Company Profile
|--------------------------------------------------------------------------
*/

Route::resource('hero', HeroController::class)->except('show');

Route::resource('layanan', LayananController::class)->except('show');

Route::resource('portofolio', PortofolioController::class)->except('show');

Route::resource('kategori-portofolio', KategoriPortofolioController::class)->except('show');

Route::resource('tutorial', TutorialController::class)->except('show');

Route::resource('materi-tutorial', MateriTutorialController::class)->except('show');

Route::resource('kategori-tutorial', KategoriTutorialController::class)->except('show');

Route::resource('artikel', ArtikelController::class)->except('show');

Route::resource('kategori-artikel', KategoriArtikelController::class)->except('show');

Route::resource('faq', FaqController::class)->except('show');

// Menu Pengaturan Sistem: hanya Super Administrator.
Route::middleware('can:kelola-sistem')->group(function (): void {
    // Singleton: identitas selalu satu baris, jadi tanpa index/create/destroy.
    Route::singleton('identitas', IdentitasController::class)->only(['edit', 'update']);

    // Tampil/tidaknya modul di website (Manajemen Modul): baris tetap per modul, hanya status yang diubah.
    Route::resource('modul-website', ModulWebsiteController::class)->only(['index', 'edit', 'update']);

    // Template tampilan frontend (Manajemen Template). Pratinjau = beranda dengan nilai isian form, dibuka di
    // tab baru tanpa menyimpan apa pun.
    Route::get('template/pratinjau', [TemplateController::class, 'pratinjau'])->name('template.pratinjau');
    Route::resource('template', TemplateController::class)->except('show');
});
