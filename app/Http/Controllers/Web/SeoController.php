<?php

namespace App\Http\Controllers\Web;

use App\Enums\CompanyProfile\JenisModul;
use App\Http\Controllers\Controller;
use App\Models\CompanyProfile\Artikel;
use App\Models\CompanyProfile\KategoriArtikel;
use App\Models\CompanyProfile\KategoriTutorial;
use App\Models\CompanyProfile\Portofolio;
use App\Models\CompanyProfile\Tutorial;
use App\Services\CompanyProfile\ModulWebsiteService;
use App\Support\Seo;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Http\Response;

/**
 * Berkas untuk perayap mesin pencari, berlaku untuk seluruh situs (semua modul). Alamatnya dibentuk dari APP_URL
 * agar selalu menunjuk alamat utama, termasuk saat dibuka lewat www.
 */
class SeoController extends Controller
{
    /**
     * Beranda, daftar artikel, kategori yang punya artikel terbit, semua artikel terbit, daftar portofolio, semua
     * portofolio, daftar tutorial, kategori yang punya tutorial tayang, serta semua tutorial yang tayang beserta
     * materinya. Halaman paginasi tidak dimasukkan karena perayap menemukannya lewat tautan di halaman daftar. Modul
     * yang nonaktif di Manajemen Modul tidak punya halaman (404), jadi koleksinya kosong dan daftarnya dilewati view.
     */
    public function sitemap(ModulWebsiteService $modul): Response
    {
        $artikel = $modul->tampil(JenisModul::Artikel)
            ? Artikel::query()->terbit()
                ->orderByDesc('tanggal')
                ->orderByDesc('id_artikel')
                ->get(['id_artikel', 'id_kategori_artikel', 'slug', 'tanggal', 'updated_at'])
            : new Collection;

        // Kategori tanpa artikel terbit belum punya halaman, jadi diambil dari artikel di atas.
        $kategori = KategoriArtikel::query()
            ->whereIn('id_kategori_artikel', $artikel->pluck('id_kategori_artikel')->unique())
            ->orderBy('nama_kategori')
            ->get(['id_kategori_artikel', 'slug', 'updated_at']);

        $portofolio = $modul->tampil(JenisModul::Portofolio)
            ? Portofolio::query()->terbaru()->get(['id_portofolio', 'slug', 'updated_at'])
            : new Collection;

        $tutorial = $modul->tampil(JenisModul::Tutorial)
            ? Tutorial::query()->tayang()->terbaru()
                ->with('materiTutorial:id_materi_tutorial,id_tutorial,slug,updated_at')
                ->get(['id_tutorial', 'id_kategori_tutorial', 'slug', 'updated_at'])
            : new Collection;

        // Sama dengan kategori artikel: kategori tanpa tutorial tayang belum punya halaman.
        $kategoriTutorial = KategoriTutorial::query()
            ->whereIn('id_kategori_tutorial', $tutorial->pluck('id_kategori_tutorial')->unique())
            ->orderBy('nama_kategori')
            ->get(['id_kategori_tutorial', 'slug', 'updated_at']);

        return response()->view('seo.sitemap', compact('artikel', 'kategori', 'portofolio', 'tutorial', 'kategoriTutorial', 'modul'))
            ->header('Content-Type', 'application/xml; charset=UTF-8');
    }

    /**
     * Panel admin & login tidak untuk dirayapi. Sitemap hanya disebut bila situs boleh diindeks (config/seo.php).
     */
    public function robots(): Response
    {
        $baris = ['User-agent: *', 'Disallow: /admin', 'Disallow: /login'];

        if (config('seo.indeks')) {
            array_push($baris, '', 'Sitemap: '.Seo::alamat(route('sitemap', [], false)));
        }

        return response(implode("\n", $baris)."\n")
            ->header('Content-Type', 'text/plain; charset=UTF-8');
    }
}
