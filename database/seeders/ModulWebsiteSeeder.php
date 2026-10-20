<?php

namespace Database\Seeders;

use App\Enums\CompanyProfile\JenisModul;
use App\Models\CompanyProfile\ModulWebsite;
use Illuminate\Database\Seeder;

class ModulWebsiteSeeder extends Seeder
{
    /**
     * Satu baris per modul website, semuanya aktif. Hanya modul yang belum punya baris yang dibuat (termasuk yang
     * terhapus), jadi aman dijalankan ulang tanpa menimpa status yang diatur admin.
     */
    public function run(): void
    {
        foreach (JenisModul::cases() as $jenis) {
            ModulWebsite::withTrashed()->firstOrCreate(['jenis' => $jenis], ['status_aktif' => true]);
        }
    }
}
