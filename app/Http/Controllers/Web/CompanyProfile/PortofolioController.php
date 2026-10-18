<?php

namespace App\Http\Controllers\Web\CompanyProfile;

use App\Http\Controllers\Controller;
use App\Models\CompanyProfile\Layanan;
use App\Models\CompanyProfile\Portofolio;
use Illuminate\View\View;

class PortofolioController extends Controller
{
    // Lima baris dua kartu di kolom utama, sama dengan daftar artikel: halaman penuh tanpa kartu yatim.
    public const PER_HALAMAN = 10;

    // Portofolio lainnya di kolom samping detail portofolio.
    public const JUMLAH_LAINNYA = 5;

    public function index(): View
    {
        $portofolio = Portofolio::query()->untukKartu()->paginate(self::PER_HALAMAN);

        // Nomor halaman di luar jangkauan (mis. ?page=99) bukan halaman kosong yang sah.
        abort_if($portofolio->isEmpty() && $portofolio->currentPage() > 1, 404);

        // "Layanan kami" di kolom samping; footer memakai koleksi yang sama sehingga SitusComposer tidak query ulang.
        $layanan = Layanan::untukTautan();

        return view('web.portofolio.index', compact('portofolio', 'layanan'));
    }

    public function show(Portofolio $portofolio): View
    {
        // Gambar tambahan untuk carousel detail.
        $portofolio->load(['gambarPortofolio', 'kategoriPortofolio']);

        // Tanpa kolom deskripsi: kolom samping hanya menampilkan gambar, judul, dan kategori.
        $portofolioLainnya = Portofolio::query()->untukKartu()->whereKeyNot($portofolio->getKey())
            ->limit(self::JUMLAH_LAINNYA)
            ->get(['id_portofolio', 'id_kategori_portofolio', 'judul', 'slug', 'gambar']);
        $layanan = Layanan::untukTautan();

        return view('web.portofolio.show', compact('portofolio', 'portofolioLainnya', 'layanan'));
    }
}
