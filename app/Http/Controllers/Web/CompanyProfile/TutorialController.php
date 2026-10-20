<?php

namespace App\Http\Controllers\Web\CompanyProfile;

use App\Enums\CompanyProfile\StatusPublikasi;
use App\Http\Controllers\Controller;
use App\Models\CompanyProfile\KategoriTutorial;
use App\Models\CompanyProfile\Layanan;
use App\Models\CompanyProfile\Tutorial;
use Illuminate\View\View;

class TutorialController extends Controller
{
    // Sama dengan daftar artikel. Dipakai juga halaman kategori tutorial.
    public const PER_HALAMAN = 10;

    public function index(): View
    {
        $tutorial = Tutorial::query()->untukKartu()->paginate(self::PER_HALAMAN);

        // Nomor halaman di luar jangkauan (mis. ?page=99) bukan halaman kosong yang sah.
        abort_if($tutorial->isEmpty() && $tutorial->currentPage() > 1, 404);

        $daftarKategori = KategoriTutorial::untukSamping();
        // "Layanan kami" di kolom samping; footer memakai koleksi yang sama sehingga SitusComposer tidak query ulang.
        $layanan = Layanan::untukTautan();

        return view('web.tutorial.index', compact('tutorial', 'daftarKategori', 'layanan'));
    }

    public function show(Tutorial $tutorial): View
    {
        // Draf belum tayang: diperlakukan sama dengan alamat yang tidak ada.
        abort_unless($tutorial->status_publikasi === StatusPublikasi::Terbit, 404);

        // Tanpa kolom deskripsi (isi materi): daftar materi cukup judul dan alamatnya.
        $daftarMateri = $tutorial->materiTutorial()->get(['id_materi_tutorial', 'judul', 'slug']);
        // Tutorial tanpa materi belum tayang, sama seperti draf.
        abort_if($daftarMateri->isEmpty(), 404);

        $tutorial->load('kategoriTutorial');
        $daftarKategori = KategoriTutorial::untukSamping();
        $layanan = Layanan::untukTautan();

        return view('web.tutorial.show', compact('tutorial', 'daftarMateri', 'daftarKategori', 'layanan'));
    }
}
