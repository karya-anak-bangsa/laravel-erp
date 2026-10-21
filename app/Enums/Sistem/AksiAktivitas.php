<?php

namespace App\Enums\Sistem;

use App\Enums\Concerns\MenyediakanOpsi;

/**
 * Jenis aktivitas di Log Aktivitas: perubahan data lewat panel admin, serta masuk & keluar panel admin (label sama
 * dengan tombol Masuk di halaman login dan Keluar di menu pengguna).
 */
enum AksiAktivitas: string
{
    use MenyediakanOpsi;

    case Tambah = 'tambah';
    case Ubah = 'ubah';
    case Hapus = 'hapus';
    case AktifkanKembali = 'aktifkan_kembali';
    case Masuk = 'masuk';
    case Keluar = 'keluar';

    public function label(): string
    {
        return match ($this) {
            self::Tambah => 'Tambah',
            self::Ubah => 'Ubah',
            self::Hapus => 'Hapus',
            self::AktifkanKembali => 'Aktifkan kembali',
            self::Masuk => 'Masuk',
            self::Keluar => 'Keluar',
        };
    }
}
