<?php

namespace App\Http\Controllers\Web\CompanyProfile;

use App\Enums\CompanyProfile\StatusPublikasi;
use App\Http\Controllers\Controller;
use App\Models\CompanyProfile\Artikel;
use App\Models\CompanyProfile\Layanan;
use Illuminate\View\View;

class ArtikelController extends Controller
{
    // Lima baris dua kartu di kolom utama Full Color: halaman penuh tanpa kartu yatim (pilihan pemilik 2026-10-15).
    public const PER_HALAMAN = 10;

    // Artikel terbaru di kolom samping detail artikel.
    public const JUMLAH_TERBARU = 5;

    public function index(): View
    {
        $artikel = Artikel::query()->untukKartu()->paginate(self::PER_HALAMAN);

        // Nomor halaman di luar jangkauan (mis. ?page=99) bukan halaman kosong yang sah.
        abort_if($artikel->isEmpty() && $artikel->currentPage() > 1, 404);

        // "Layanan kami" di kolom samping; footer memakai koleksi yang sama sehingga SitusComposer tidak query ulang.
        $layanan = Layanan::untukTautan();

        return view('web.artikel.index', compact('artikel', 'layanan'));
    }

    public function show(Artikel $artikel): View
    {
        // Draf belum tayang: diperlakukan sama dengan alamat yang tidak ada.
        abort_unless($artikel->status_publikasi === StatusPublikasi::Terbit, 404);

        $artikel->load('kategoriArtikel:id_kategori_artikel,nama_kategori');
        // Tanpa kolom deskripsi: kolom samping hanya menampilkan gambar, judul, dan tanggal.
        $artikelTerbaru = Artikel::query()->terbaru()->whereKeyNot($artikel->getKey())
            ->limit(self::JUMLAH_TERBARU)
            ->get(['id_artikel', 'judul', 'slug', 'gambar', 'tanggal']);
        $layanan = Layanan::untukTautan();

        return view('web.artikel.show', compact('artikel', 'artikelTerbaru', 'layanan'));
    }
}
