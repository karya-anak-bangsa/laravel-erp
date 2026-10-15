<?php

namespace Database\Seeders;

use App\Models\CompanyProfile\KategoriArtikel;
use App\Services\CompanyProfile\KategoriArtikelService;
use Illuminate\Database\Seeder;

class KategoriArtikelSeeder extends Seeder
{
    /**
     * Kategori awal artikel berupa topik teknologi (pilihan pemilik 2026-10-16), urut abjad; namanya bisa diubah admin
     * lewat panel. Gambar artikel contoh memakai ilustrasi per kategori: database/seeders/berkas/artikel/<slug>.webp.
     */
    public const NAMA = [
        'Database',
        'Flutter',
        'JavaScript',
        'Keamanan Siber',
        'Kecerdasan Buatan',
        'Laptop Gaming',
        'Laptop Thin and Light',
        'Laravel',
        'Python',
        'Sistem Operasi',
    ];

    public function run(KategoriArtikelService $kategoriArtikelService): void
    {
        // Termasuk yang terhapus agar seeder aman dijalankan ulang tanpa menimpa isian admin.
        if (KategoriArtikel::withTrashed()->exists()) {
            return;
        }

        foreach (self::NAMA as $nama) {
            $kategoriArtikelService->simpan(['nama_kategori' => $nama]);
        }
    }
}
