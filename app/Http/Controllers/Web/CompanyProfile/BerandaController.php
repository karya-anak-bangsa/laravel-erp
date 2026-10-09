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
        // Proyek terbaru di depan; id memecah created_at kembar dari seeder.
        $portofolio = Portofolio::query()->latest()->latest('id_portofolio')->limit(6)->get();
        $artikel = Artikel::query()
            ->terbit()
            ->with('kategoriArtikel:id_kategori_artikel,nama_kategori')
            ->orderByDesc('tanggal')
            ->orderByDesc('id_artikel')
            ->limit(3)
            ->get();
        $faq = Faq::query()->berurutan()->get();

        return view('web.beranda', compact('hero', 'layanan', 'portofolio', 'artikel', 'faq'));
    }
}
