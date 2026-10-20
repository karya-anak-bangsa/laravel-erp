<?php

use App\Http\Controllers\Auth\LoginController;
use App\Http\Controllers\Web\CompanyProfile\ArtikelController;
use App\Http\Controllers\Web\CompanyProfile\BerandaController;
use App\Http\Controllers\Web\CompanyProfile\KategoriArtikelController;
use App\Http\Controllers\Web\CompanyProfile\KategoriTutorialController;
use App\Http\Controllers\Web\CompanyProfile\KontakKamiController;
use App\Http\Controllers\Web\CompanyProfile\MateriTutorialController;
use App\Http\Controllers\Web\CompanyProfile\PortofolioController;
use App\Http\Controllers\Web\CompanyProfile\TutorialController;
use App\Http\Controllers\Web\SeoController;
use Illuminate\Routing\RedirectController;
use Illuminate\Support\Facades\Route;

// Frontend publik: di luar grup guest agar admin yang sedang login tetap bisa melihat situs.
Route::get('/', BerandaController::class)->name('beranda');

Route::get('artikel', [ArtikelController::class, 'index'])->name('artikel.index');
Route::get('artikel/{artikel:slug}', [ArtikelController::class, 'show'])->name('artikel.show');
Route::get('artikel/kategori/{kategoriArtikel:slug}', [KategoriArtikelController::class, 'show'])->name('kategori-artikel.show');

Route::get('portofolio', [PortofolioController::class, 'index'])->name('portofolio.index');
Route::get('portofolio/{portofolio:slug}', [PortofolioController::class, 'show'])->name('portofolio.show');

Route::get('tutorial', [TutorialController::class, 'index'])->name('tutorial.index');
// Sebelum rute materi yang sama-sama dua segmen; slug tutorial "kategori" dicadangkan TutorialService.
Route::get('tutorial/kategori/{kategoriTutorial:slug}', [KategoriTutorialController::class, 'show'])->name('kategori-tutorial.show');
Route::get('tutorial/{tutorial:slug}', [TutorialController::class, 'show'])->name('tutorial.show');
// Slug materi unik per tutorial, jadi materi hanya dicari di antara materi tutorial di alamat itu.
Route::get('tutorial/{tutorial:slug}/{materiTutorial:slug}', [MateriTutorialController::class, 'show'])
    ->scopeBindings()
    ->name('materi-tutorial.show');

Route::post('kontak', [KontakKamiController::class, 'store'])
    ->middleware('throttle:kontak')
    ->name('kontak-kami.store');
// Halaman kontak tersendiri belum ada; GET (mis. muat ulang setelah kirim) diarahkan ke formulir di beranda.
// Bukan Route::redirect(): itu mendaftarkan semua method dan akan menimpa rute POST di atas.
Route::get('kontak', RedirectController::class)
    ->defaults('destination', '/#kontak')
    ->defaults('status', 302);

// Untuk perayap mesin pencari, dibentuk dari APP_URL. public/robots.txt sengaja tidak ada: berkas statis
// didahulukan server sehingga rute ini tidak akan terpakai.
Route::get('sitemap.xml', [SeoController::class, 'sitemap'])->name('sitemap');
Route::get('robots.txt', [SeoController::class, 'robots'])->name('robots');

Route::middleware('guest')->group(function () {
    Route::get('login', [LoginController::class, 'create'])->name('login');
    Route::post('login', [LoginController::class, 'store'])->name('login.store');
});

Route::post('logout', [LoginController::class, 'destroy'])
    ->middleware('auth')
    ->name('logout');
