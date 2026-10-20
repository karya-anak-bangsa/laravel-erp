<?php

namespace App\Http\Controllers\Web\CompanyProfile;

use App\Http\Controllers\Controller;
use App\Models\CompanyProfile\KategoriTutorial;
use App\Models\CompanyProfile\Layanan;
use App\Models\CompanyProfile\Tutorial;
use Illuminate\View\View;

class KategoriTutorialController extends Controller
{
    public function show(KategoriTutorial $kategoriTutorial): View
    {
        $tutorial = Tutorial::query()
            ->whereBelongsTo($kategoriTutorial)
            ->untukKartu()
            ->paginate(TutorialController::PER_HALAMAN);

        // Kategori tanpa tutorial tayang belum punya halaman; nomor halaman di luar jangkauan juga bukan halaman yang sah.
        abort_if($tutorial->isEmpty(), 404);

        $daftarKategori = KategoriTutorial::untukSamping();
        $layanan = Layanan::untukTautan();

        return view('web.kategori-tutorial.show', compact('kategoriTutorial', 'tutorial', 'daftarKategori', 'layanan'));
    }
}
