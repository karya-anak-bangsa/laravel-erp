<?php

namespace App\Http\Controllers\Web\CompanyProfile;

use App\Enums\CompanyProfile\StatusPublikasi;
use App\Http\Controllers\Controller;
use App\Models\CompanyProfile\Layanan;
use App\Models\CompanyProfile\MateriTutorial;
use App\Models\CompanyProfile\Tutorial;
use Illuminate\View\View;

class MateriTutorialController extends Controller
{
    /**
     * Materi milik tutorial lain atau terhapus sudah ditolak route binding (404); materi tutorial draf belum tayang.
     */
    public function show(Tutorial $tutorial, MateriTutorial $materiTutorial): View
    {
        abort_unless($tutorial->status_publikasi === StatusPublikasi::Terbit, 404);

        // Daftar materi di kolom samping sekaligus sumber nomor ("Materi 2 dari 8") dan materi sebelumnya/berikutnya.
        $daftarMateri = $tutorial->materiTutorial()->get(['id_materi_tutorial', 'judul', 'slug']);
        $indeks = (int) $daftarMateri->search(fn (MateriTutorial $item): bool => $item->is($materiTutorial));
        $sebelumnya = $indeks > 0 ? $daftarMateri->get($indeks - 1) : null;
        $berikutnya = $daftarMateri->get($indeks + 1);
        $layanan = Layanan::untukTautan();

        return view('web.materi-tutorial.show', compact('tutorial', 'materiTutorial', 'daftarMateri', 'indeks', 'sebelumnya', 'berikutnya', 'layanan'));
    }
}
