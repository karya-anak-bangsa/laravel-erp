<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    use WithoutModelEvents;

    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        $this->call([
            PenggunaSeeder::class,
            IdentitasSeeder::class,
            HeroSeeder::class,
            LayananSeeder::class,
        ]);

        // Data contoh hanya untuk pengembangan; di produksi dijalankan manual bila perlu.
        // Wajib setelah HeroSeeder: HeroSeeder dilewati bila tabel hero sudah berisi.
        if (app()->isLocal()) {
            $this->call([
                HeroDummySeeder::class,
                PortofolioDummySeeder::class,
            ]);
        }
    }
}
