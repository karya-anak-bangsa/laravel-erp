<?php

namespace Database\Seeders;

use App\Models\CompanyProfile\KategoriPortofolio;
use Illuminate\Database\Seeder;

class KategoriPortofolioSeeder extends Seeder
{
    /**
     * Kategori awal portofolio = layanan utama perusahaan. Urutannya bermakna: kartu portofolio di beranda tampil menurut
     * urutan kategori dibuat (keputusan pemilik 2026-10-11, dipertahankan 2026-10-18); namanya bisa diubah admin.
     */
    public const NAMA = ['Website', 'Mobile Apps', 'Pelatihan IT', 'Sertifikasi IT', 'Bootcamp'];

    public function run(): void
    {
        // Termasuk yang terhapus agar seeder aman dijalankan ulang tanpa menimpa isian admin.
        if (KategoriPortofolio::withTrashed()->exists()) {
            return;
        }

        foreach (self::NAMA as $nama) {
            KategoriPortofolio::create(['nama_kategori' => $nama]);
        }
    }
}
