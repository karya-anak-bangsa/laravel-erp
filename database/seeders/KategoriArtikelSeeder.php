<?php

namespace Database\Seeders;

use App\Models\CompanyProfile\KategoriArtikel;
use Illuminate\Database\Seeder;

class KategoriArtikelSeeder extends Seeder
{
    /**
     * Kategori awal artikel; namanya bisa diubah admin lewat panel.
     */
    public function run(): void
    {
        // Termasuk yang terhapus agar seeder aman dijalankan ulang tanpa menimpa isian admin.
        if (KategoriArtikel::withTrashed()->exists()) {
            return;
        }

        foreach (['Teknologi', 'Pelatihan & Sertifikasi', 'Tips & Tutorial', 'Kabar Perusahaan'] as $nama) {
            KategoriArtikel::create(['nama_kategori' => $nama]);
        }
    }
}
