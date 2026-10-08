<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

class CompanyProfileSeeder extends Seeder
{
    /**
     * Data awal Company Profile yang sama persis di lokal dan produksi. Terpisah dari
     * PenggunaSeeder agar bisa dijalankan di server, tempat ADMIN_PASSWORD sudah dikosongkan.
     * Setiap seeder aman dijalankan ulang tanpa menimpa isian admin.
     */
    public function run(): void
    {
        $this->call([
            IdentitasSeeder::class,
            // HeroDummySeeder wajib setelah HeroSeeder: HeroSeeder dilewati bila tabel hero sudah berisi.
            HeroSeeder::class,
            HeroDummySeeder::class,
            LayananSeeder::class,
            PortofolioDummySeeder::class,
            FaqSeeder::class,
            KategoriArtikelSeeder::class,
            ArtikelDummySeeder::class,
        ]);
    }
}
