<?php

namespace App\Http\Controllers\Web\CompanyProfile;

use App\Enums\CompanyProfile\StatusPublikasi;
use App\Http\Controllers\Controller;
use App\Models\CompanyProfile\Layanan;
use App\Models\CompanyProfile\Tutorial;
use Illuminate\View\View;

class TutorialController extends Controller
{
    // Lima baris dua kartu di kolom utama, sama dengan daftar portofolio: halaman penuh tanpa kartu yatim.
    public const PER_HALAMAN = 10;

    public function index(): View
    {
        $tutorial = Tutorial::query()
            ->tayang()
            // Nama kategori untuk pita kartu.
            ->with('kategoriTutorial:id_kategori_tutorial,nama_kategori')
            ->withCount('materiTutorial')
            ->terbaru()
            ->paginate(self::PER_HALAMAN);

        // Nomor halaman di luar jangkauan (mis. ?page=99) bukan halaman kosong yang sah.
        abort_if($tutorial->isEmpty() && $tutorial->currentPage() > 1, 404);

        // "Layanan kami" di kolom samping; footer memakai koleksi yang sama sehingga SitusComposer tidak query ulang.
        $layanan = Layanan::untukTautan();

        return view('web.tutorial.index', compact('tutorial', 'layanan'));
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
        $layanan = Layanan::untukTautan();

        return view('web.tutorial.show', compact('tutorial', 'daftarMateri', 'layanan'));
    }
}
