<?php

namespace App\Services\CompanyProfile;

use App\Enums\CompanyProfile\JenisModul;
use App\Models\CompanyProfile\ModulWebsite;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;
use Throwable;

/**
 * Status tampil modul website (Pengaturan Sistem › Manajemen Modul) untuk frontend publik: navbar, footer, seksi
 * beranda, kolom samping, rute halaman modul, dan sitemap.
 */
class ModulWebsiteService
{
    // Dibaca berkali-kali per halaman (rute, controller, composer tiap partial); cache cukup dibaca sekali. Request
    // diambil saat dipanggil, bukan lewat konstruktor: instance controller disimpan rutenya dan bisa dipakai lagi oleh
    // request berikutnya (test, Octane).
    private const ATRIBUT_REQUEST = 'situs.modul-website';

    public function tampil(JenisModul $jenis): bool
    {
        return $this->statusTampil()[$jenis->value];
    }

    /**
     * Status setiap modul [jenis => tampil], di-cache tanpa batas waktu dan dibuang lewat event model ModulWebsite.
     * Modul yang belum punya baris dianggap aktif. Bila database tidak terbaca, semua modul dianggap aktif agar
     * halaman publik (termasuk halaman error) tetap utuh.
     *
     * @return array<string, bool>
     */
    public function statusTampil(): array
    {
        $atribut = app(Request::class)->attributes;

        if (! $atribut->has(self::ATRIBUT_REQUEST)) {
            try {
                /** @var array<string, bool> $tersimpan */
                $tersimpan = Cache::rememberForever(ModulWebsite::KUNCI_CACHE, fn (): array => ModulWebsite::query()
                    ->toBase()
                    ->pluck('status_aktif', 'jenis')
                    ->map(fn (mixed $aktif): bool => (bool) $aktif)
                    ->all());
            } catch (Throwable $e) {
                Log::warning('Status modul website tidak terbaca, semua modul dianggap aktif.', ['galat' => $e->getMessage()]);
                $tersimpan = [];
            }

            $status = [];

            foreach (JenisModul::cases() as $jenis) {
                $status[$jenis->value] = $tersimpan[$jenis->value] ?? true;
            }

            $atribut->set(self::ATRIBUT_REQUEST, $status);
        }

        return $atribut->get(self::ATRIBUT_REQUEST);
    }
}
