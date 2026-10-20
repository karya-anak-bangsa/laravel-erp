<?php

namespace App\Services\CompanyProfile;

use App\Enums\CompanyProfile\JenisModul;
use App\Models\CompanyProfile\ModulWebsite;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;
use Throwable;

/**
 * Status dan urutan modul website (Pengaturan Sistem › Manajemen Modul) untuk menu navbar, Tautan Cepat footer, dan
 * seksi beranda.
 */
class ModulWebsiteService
{
    // Dibaca berkali-kali per halaman (controller, composer tiap partial); cache cukup dibaca sekali. Request diambil
    // saat dipanggil, bukan lewat konstruktor: instance controller disimpan rutenya dan bisa dipakai lagi oleh request
    // berikutnya (test, Octane).
    private const ATRIBUT_REQUEST = 'situs.modul-website';

    public function tampil(JenisModul $jenis): bool
    {
        return in_array($jenis, $this->urutanTampil(), true);
    }

    /**
     * Modul aktif menurut urutan_ke; angka yang sama mengikuti urutan bawaan. Modul yang belum punya baris dianggap aktif
     * dengan urutan bawaan. Bila database tidak terbaca, semua modul tampil dengan urutan bawaan agar halaman publik
     * (termasuk halaman error) tetap utuh.
     *
     * @return list<JenisModul>
     */
    public function urutanTampil(): array
    {
        $atribut = app(Request::class)->attributes;

        if (! $atribut->has(self::ATRIBUT_REQUEST)) {
            $tersimpan = $this->tersimpan();
            $urutan = fn (JenisModul $jenis): int => $tersimpan[$jenis->value]['urutan_ke'] ?? $jenis->urutanBawaan();

            $modul = array_values(array_filter(JenisModul::cases(), fn (JenisModul $jenis): bool => $tersimpan[$jenis->value]['status_aktif'] ?? true));
            // usort stabil (PHP 8), jadi angka yang sama tetap seurut JenisModul.
            usort($modul, fn (JenisModul $a, JenisModul $b): int => $urutan($a) <=> $urutan($b));

            $atribut->set(self::ATRIBUT_REQUEST, $modul);
        }

        return $atribut->get(self::ATRIBUT_REQUEST);
    }

    /**
     * Isi tb_modul_website [jenis => [urutan_ke, status_aktif]], di-cache tanpa batas waktu dan dibuang lewat event model
     * ModulWebsite. Yang disimpan hanya nilai skalar: store database di produksi tidak menerima objek.
     *
     * @return array<string, array{urutan_ke: int, status_aktif: bool}>
     */
    private function tersimpan(): array
    {
        try {
            return Cache::rememberForever(ModulWebsite::KUNCI_CACHE, fn (): array => ModulWebsite::query()
                ->toBase()
                ->get(['jenis', 'urutan_ke', 'status_aktif'])
                ->mapWithKeys(fn (object $baris): array => [$baris->jenis => [
                    'urutan_ke' => (int) $baris->urutan_ke,
                    'status_aktif' => (bool) $baris->status_aktif,
                ]])
                ->all());
        } catch (Throwable $e) {
            Log::warning('Status modul website tidak terbaca, semua modul tampil dengan urutan bawaan.', ['galat' => $e->getMessage()]);

            return [];
        }
    }
}
