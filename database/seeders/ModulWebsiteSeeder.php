<?php

namespace Database\Seeders;

use App\Enums\CompanyProfile\JenisModul;
use App\Models\CompanyProfile\ModulWebsite;
use Illuminate\Database\Seeder;

class ModulWebsiteSeeder extends Seeder
{
    /**
     * Satu baris per modul website, semuanya aktif dengan urutan bawaan (= susunan menu dan seksi beranda semula).
     * Hanya modul yang belum punya baris yang dibuat (termasuk yang terhapus), jadi aman dijalankan ulang tanpa menimpa
     * status dan urutan yang diatur admin.
     */
    public function run(): void
    {
        foreach (JenisModul::cases() as $jenis) {
            ModulWebsite::withTrashed()->firstOrCreate(['jenis' => $jenis], [
                'urutan_ke' => $jenis->urutanBawaan(),
                'status_aktif' => true,
            ]);
        }
    }
}
