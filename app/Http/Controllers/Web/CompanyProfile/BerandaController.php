<?php

namespace App\Http\Controllers\Web\CompanyProfile;

use App\Http\Controllers\Controller;
use App\Models\CompanyProfile\Artikel;
use App\Models\CompanyProfile\Faq;
use App\Models\CompanyProfile\Hero;
use App\Models\CompanyProfile\Layanan;
use App\Models\CompanyProfile\Portofolio;
use Illuminate\View\View;

class BerandaController extends Controller
{
    public function __invoke(): View
    {
        $hero = Hero::query()->aktif()->latest('id_hero')->first();
        $layanan = Layanan::query()->berurutan()->get();
        // Enam proyek terbaru, tampil menurut urutan kategori dibuat: kategori awal mengikuti urutan layanan
        // (KategoriPortofolioSeeder), kategori baru di belakangnya. sortBy stabil, jadi dalam satu kategori yang
        // terbaru tetap di depan.
        $portofolio = Portofolio::query()->untukKartu()->limit(6)->get()
            ->sortBy('id_kategori_portofolio')
            ->values();
        $artikel = Artikel::query()->untukKartu()->limit(3)->get();
        $faq = Faq::query()->berurutan()->get();

        return view('web.beranda', compact('hero', 'layanan', 'portofolio', 'artikel', 'faq'));
    }
}
