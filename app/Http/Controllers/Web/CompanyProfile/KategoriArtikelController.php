<?php

namespace App\Http\Controllers\Web\CompanyProfile;

use App\Http\Controllers\Controller;
use App\Models\CompanyProfile\KategoriArtikel;
use App\Models\CompanyProfile\Layanan;
use Illuminate\View\View;

class KategoriArtikelController extends Controller
{
    public function show(KategoriArtikel $kategoriArtikel): View
    {
        $artikel = $kategoriArtikel->artikel()->untukKartu()->paginate(ArtikelController::PER_HALAMAN);

        // Kategori tanpa artikel terbit belum punya halaman; nomor halaman di luar jangkauan juga bukan halaman yang sah.
        abort_if($artikel->isEmpty(), 404);

        $daftarKategori = KategoriArtikel::untukSamping();
        $layanan = Layanan::untukTautan();

        return view('web.kategori-artikel.show', compact('kategoriArtikel', 'artikel', 'daftarKategori', 'layanan'));
    }
}
