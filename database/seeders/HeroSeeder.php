<?php

namespace Database\Seeders;

use App\Enums\CompanyProfile\GayaCta;
use App\Models\CompanyProfile\Hero;
use Database\Seeders\Concerns\MenyalinBerkasAwal;
use Illuminate\Database\Seeder;

class HeroSeeder extends Seeder
{
    use MenyalinBerkasAwal;

    /**
     * Membuat satu hero awal yang aktif; isinya bisa diubah admin lewat panel.
     */
    public function run(): void
    {
        // Termasuk yang terhapus agar seeder aman dijalankan ulang tanpa menimpa isian admin.
        if (Hero::withTrashed()->exists()) {
            return;
        }

        Hero::create([
            'judul' => 'Solusi Digital & Talenta IT untuk Indonesia',
            'deskripsi' => 'Kami membantu bisnis tumbuh lewat website dan aplikasi mobile, serta menyiapkan talenta IT melalui pelatihan, sertifikasi, dan bootcamp.',
            'gambar' => $this->salinBerkasAwal(public_path('img/hero.webp'), Hero::FOLDER, Hero::DISK),
            'keyword' => ['Website', 'Mobile Apps', 'Pelatihan IT', 'Sertifikasi IT', 'Bootcamp'],
            'cta' => [
                ['label' => 'Hubungi Kami', 'url' => '#kontak', 'gaya' => GayaCta::Primary->value],
                ['label' => 'Lihat Portofolio', 'url' => '/portofolio', 'gaya' => GayaCta::Secondary->value],
            ],
            'status_aktif' => true,
        ]);
    }
}
