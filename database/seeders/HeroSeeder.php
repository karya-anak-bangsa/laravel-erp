<?php

namespace Database\Seeders;

use App\Enums\CompanyProfile\GayaCta;
use App\Models\CompanyProfile\Hero;
use App\Support\TeksHtml;
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
            'judul' => 'Solusi IT terbaik untuk digitalisasi bisnis Anda',
            'deskripsi' => TeksHtml::dariTeksPolos('Kami menghadirkan solusi digital modern yang membantu bisnis berkembang lebih cepat, efisien, dan siap menghadapi era teknologi.'),
            'gambar' => $this->salinBerkasAwal(public_path('img/hero.webp'), Hero::FOLDER, Hero::DISK),
            'keyword' => ['Website', 'Mobile Apps', 'Pelatihan IT', 'Sertifikasi IT', 'Bootcamp'],
            'cta' => [
                // Sama dengan link WhatsApp di IdentitasSeeder agar semua tombol WhatsApp menuju nomor yang sama.
                ['label' => 'Hubungi Kami', 'url' => 'https://wa.me/6281932855946', 'gaya' => GayaCta::Primary->value],
                ['label' => 'Lihat Portofolio', 'url' => '#portofolio', 'gaya' => GayaCta::Secondary->value],
            ],
            'status_aktif' => true,
        ]);
    }
}
