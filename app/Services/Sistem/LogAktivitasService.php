<?php

namespace App\Services\Sistem;

use App\Enums\Sistem\AksiAktivitas;
use App\Models\Pengguna;
use App\Models\Sistem\LogAktivitas;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;

/**
 * Penulis Log Aktivitas (Pengaturan Sistem): dipanggil trait MencatatAktivitas untuk perubahan data dan listener event
 * Login/Logout di AppServiceProvider. Baris log hanya ditambah, tidak pernah diubah atau dihapus.
 */
class LogAktivitasService
{
    /**
     * Perubahan data oleh pengguna yang sedang login. Tanpa pengguna login (seeder, tinker, factory test) tidak dicatat
     * karena pelakunya tidak diketahui.
     *
     * @param  string  $nama  nama data saat aksi terjadi
     * @param  list<string>  $isian  label isian yang berubah (aksi Ubah)
     */
    public function catatData(AksiAktivitas $aksi, Model $subjek, string $nama, array $isian = []): void
    {
        $pengguna = Auth::user();

        if ($pengguna instanceof Pengguna) {
            $this->catat($aksi, $pengguna, $subjek, $nama, $isian);
        }
    }

    /**
     * @param  list<string>  $isian
     */
    public function catat(AksiAktivitas $aksi, Pengguna $pengguna, ?Model $subjek = null, ?string $nama = null, array $isian = []): LogAktivitas
    {
        return LogAktivitas::create([
            'id_pengguna' => $pengguna->getKey(),
            'aksi' => $aksi,
            // Alias morph map, bukan nama class (AppServiceProvider).
            'subjek_type' => $subjek?->getMorphClass(),
            'subjek_id' => $subjek?->getKey(),
            'label_subjek' => $nama === null ? null : Str::limit($nama, 255, ''),
            'perubahan' => $isian === [] ? null : $isian,
            'alamat_ip' => request()->ip(),
        ]);
    }
}
