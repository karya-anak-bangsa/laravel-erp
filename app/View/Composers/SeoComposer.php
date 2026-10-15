<?php

namespace App\View\Composers;

use App\Models\CompanyProfile\Hero;
use App\Models\CompanyProfile\Identitas;
use App\Support\Seo;
use Illuminate\Http\Request;
use Illuminate\View\View;

/**
 * Judul, deskripsi, kanonis, robots, dan pratinjau tautan (Open Graph) untuk layouts/web/seo. Halaman mengisinya
 * lewat section — judul (tanpa akhiran nama perusahaan), deskripsi, kanonis, jenis-og, gambar-bagikan — dan yang
 * tidak diisi memakai bawaan situs. Section dibaca di sini karena layout dirender setelah isi halaman.
 */
class SeoComposer
{
    public function __construct(private readonly Request $request) {}

    public function compose(View $view): void
    {
        $halaman = $view->getFactory();
        // Isi section sudah di-escape oleh @section; dikembalikan ke teks asli karena partial mencetaknya dengan {{ }}.
        $bagian = fn (string $nama): ?string => $halaman->hasSection($nama)
            ? htmlspecialchars_decode((string) $halaman->getSection($nama), ENT_QUOTES)
            : null;

        $data = $view->getData();
        /** @var Identitas $identitas */
        $identitas = $data['identitas'];
        $judulHalaman = $bagian('judul');
        $gambar = $bagian('gambar-bagikan');
        $altGambar = $judulHalaman;

        // Bawaan gambar pratinjau tautan = hero aktif (pilihan pemilik 2026-10-15), sehingga ikut berganti bersama hero.
        // Beranda sudah memuat hero-nya; halaman lain membacanya sekali di sini.
        if ($gambar === null) {
            $hero = array_key_exists('hero', $data)
                ? $data['hero']
                : Hero::query()->aktif()->latest('id_hero')->first(['id_hero', 'judul', 'gambar']);
            $hero = $hero instanceof Hero ? $hero : null;
            $gambar = $hero?->gambar_url;
            $altGambar = $hero?->judul;
        }

        $view->with('seo', [
            // Akhiran nama perusahaan seperti halaman error (pilihan pemilik 2026-10-15), sehingga Judul Website bebas
            // dibuat deskriptif khusus beranda tanpa memanjangkan judul halaman lain.
            'judul' => filled($judulHalaman) ? $judulHalaman.' — '.$identitas->nama_perusahaan : $identitas->judul_website,
            'deskripsi' => $bagian('deskripsi') ?? $identitas->meta_deskripsi,
            'kanonis' => $bagian('kanonis') ?? Seo::alamat($this->request->getPathInfo()),
            'jenis' => $bagian('jenis-og') ?? 'website',
            'gambar' => $gambar !== null ? Seo::alamat($gambar) : null,
            'ukuranGambar' => $gambar !== null ? Seo::ukuranGambar($gambar) : null,
            'altGambar' => $altGambar,
            // Pratinjau template (panel admin) bukan halaman publik walau memakai layout yang sama.
            'indeks' => config('seo.indeks') && ($data['templatePratinjau'] ?? null) === null,
        ]);
    }
}
