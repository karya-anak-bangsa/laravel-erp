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
            HeroSeeder::class,
            LayananSeeder::class,
            KategoriPortofolioSeeder::class,
            PortofolioDummySeeder::class,
            FaqSeeder::class,
            KategoriArtikelSeeder::class,
            ArtikelDummySeeder::class,
            KategoriTutorialSeeder::class,
            TutorialDummySeeder::class,
            TemplateSeeder::class,
            ModulWebsiteSeeder::class,
        ]);
    }
}
