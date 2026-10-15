<?php

namespace App\Http\Controllers\Web\CompanyProfile;

use App\Enums\CompanyProfile\StatusPublikasi;
use App\Http\Controllers\Controller;
use App\Models\CompanyProfile\Artikel;
use Illuminate\View\View;

class ArtikelController extends Controller
{
    // Tiga baris grid tiga kolom Full Color (pilihan pemilik 2026-10-15).
    public const PER_HALAMAN = 9;

    public function index(): View
    {
        $artikel = Artikel::query()->untukKartu()->paginate(self::PER_HALAMAN);

        // Nomor halaman di luar jangkauan (mis. ?page=99) bukan halaman kosong yang sah.
        abort_if($artikel->isEmpty() && $artikel->currentPage() > 1, 404);

        return view('web.artikel.index', compact('artikel'));
    }

    public function show(Artikel $artikel): View
    {
        // Draf belum tayang: diperlakukan sama dengan alamat yang tidak ada.
        abort_unless($artikel->status_publikasi === StatusPublikasi::Terbit, 404);

        $artikel->load('kategoriArtikel:id_kategori_artikel,nama_kategori');
        $artikelLain = Artikel::query()->untukKartu()->whereKeyNot($artikel->getKey())->limit(3)->get();

        return view('web.artikel.show', compact('artikel', 'artikelLain'));
    }
}
