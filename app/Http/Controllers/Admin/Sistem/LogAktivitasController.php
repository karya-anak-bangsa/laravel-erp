<?php

namespace App\Http\Controllers\Admin\Sistem;

use App\Enums\Sistem\AksiAktivitas;
use App\Http\Controllers\Controller;
use App\Models\Pengguna;
use App\Models\Sistem\LogAktivitas;
use App\Services\Sistem\LogAktivitasService;
use Illuminate\Container\Attributes\CurrentUser;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

// Log ditulis otomatis, jadi tidak ada tambah, ubah, atau hapus per baris; dari panel log hanya bisa dikosongkan
// seluruhnya (pilihan pemilik).
class LogAktivitasController extends Controller
{
    public function index(Request $request): View
    {
        $q = $request->string('q')->trim()->value();
        $idPengguna = $request->integer('pengguna');
        $aksi = AksiAktivitas::tryFrom($request->string('aktivitas')->value());

        $logAktivitas = LogAktivitas::query()
            ->with('pengguna')
            // Tanpa filter jenis data agar kartu filter tetap satu baris (pilihan pemilik); pencarian nama data cukup.
            ->when($q !== '', fn (Builder $query) => $query->where('label_subjek', 'like', "%{$q}%"))
            ->when($idPengguna > 0, fn (Builder $query) => $query->where('id_pengguna', $idPengguna))
            ->when($aksi !== null, fn (Builder $query) => $query->where('aksi', $aksi))
            // Terbaru di atas; id = urutan kejadian, termasuk yang terjadi dalam detik yang sama.
            ->latest('id_log_aktivitas')
            ->paginate(25)
            ->withQueryString();

        return view('admin.sistem.log-aktivitas.index', [
            'logAktivitas' => $logAktivitas,
            // Tombol Kosongkan Log berlaku untuk semua log, bukan hanya hasil filter.
            'jumlahLog' => LogAktivitas::query()->count(),
            // Akun Nonaktif tetap bisa dipilih karena aktivitasnya tetap tercatat.
            'opsiPengguna' => Pengguna::withTrashed()->orderBy('nama')->pluck('nama', 'id_pengguna')->all(),
        ]);
    }

    public function kosongkan(#[CurrentUser] Pengguna $pengguna, LogAktivitasService $logAktivitasService): RedirectResponse
    {
        $logAktivitasService->kosongkan($pengguna);

        return redirect()->route('admin.log-aktivitas.index')->with('success', 'Log aktivitas berhasil dikosongkan.');
    }
}
