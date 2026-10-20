<?php

namespace App\Http\Controllers\Web\CompanyProfile;

use App\Enums\CompanyProfile\JenisModul;
use App\Http\Controllers\Controller;
use App\Models\CompanyProfile\Artikel;
use App\Models\CompanyProfile\Faq;
use App\Models\CompanyProfile\Hero;
use App\Models\CompanyProfile\Layanan;
use App\Models\CompanyProfile\Portofolio;
use App\Services\CompanyProfile\ModulWebsiteService;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\View\View;

class BerandaController extends Controller
{
    // Lewat konstruktor: pratinjau template (TemplateController::pratinjau) memanggil controller ini tanpa argumen.
    public function __construct(private readonly ModulWebsiteService $modul) {}

    public function __invoke(): View
    {
        $hero = Hero::query()->aktif()->latest('id_hero')->first();
        // Modul yang dinonaktifkan di Manajemen Modul tidak di-query; koleksi kosong membuat seksinya tidak dirender.
        $layanan = $this->modul->tampil(JenisModul::Layanan) ? Layanan::query()->berurutan()->get() : new Collection;
        // Enam proyek terbaru, tampil menurut urutan kategori dibuat: kategori awal mengikuti urutan layanan
        // (KategoriPortofolioSeeder), kategori baru di belakangnya. sortBy stabil, jadi dalam satu kategori yang
        // terbaru tetap di depan.
        $portofolio = $this->modul->tampil(JenisModul::Portofolio)
            ? Portofolio::query()->untukKartu()->limit(6)->get()->sortBy('id_kategori_portofolio')->values()
            : new Collection;
        $artikel = $this->modul->tampil(JenisModul::Artikel) ? Artikel::query()->untukKartu()->limit(3)->get() : new Collection;
        $faq = $this->modul->tampil(JenisModul::Faq) ? Faq::query()->berurutan()->get() : new Collection;

        return view('web.beranda', compact('hero', 'layanan', 'portofolio', 'artikel', 'faq'));
    }
}
