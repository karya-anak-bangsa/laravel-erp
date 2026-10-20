<?php

namespace Database\Seeders;

use App\Models\CompanyProfile\KategoriTutorial;
use App\Services\CompanyProfile\KategoriTutorialService;
use Illuminate\Database\Seeder;

class KategoriTutorialSeeder extends Seeder
{
    /**
     * Kategori awal tutorial berupa topik (pilihan pemilik 2026-10-20); namanya bisa diubah admin.
     */
    public const NAMA = ['Pemrograman Web', 'Pemrograman Mobile', 'Basis Data'];

    public function run(KategoriTutorialService $kategoriTutorialService): void
    {
        // Termasuk yang terhapus agar seeder aman dijalankan ulang tanpa menimpa isian admin.
        if (KategoriTutorial::withTrashed()->exists()) {
            return;
        }

        foreach (self::NAMA as $nama) {
            $kategoriTutorialService->simpan(['nama_kategori' => $nama]);
        }
    }
}
