<?php

namespace Database\Seeders;

use App\Enums\CompanyProfile\GayaCta;
use App\Models\CompanyProfile\Hero;
use App\Support\TeksHtml;
use Database\Seeders\Concerns\MenyalinBerkasAwal;
use Illuminate\Database\Seeder;

class HeroDummySeeder extends Seeder
{
    use MenyalinBerkasAwal;

    /**
     * Hero contoh (nonaktif) untuk mencoba aturan "hanya satu hero aktif".
     * Bagian dari CompanyProfileSeeder (lokal & produksi), setelah HeroSeeder.
     */
    public function run(): void
    {
        foreach ($this->daftarHero() as $berkas => $hero) {
            // Berdasarkan judul agar aman dijalankan ulang dan tidak menyalin gambar dua kali.
            if (Hero::withTrashed()->where('judul', $hero['judul'])->exists()) {
                continue;
            }

            Hero::create([
                ...$hero,
                'deskripsi' => TeksHtml::dariTeksPolos($hero['deskripsi']),
                'gambar' => $this->salinBerkasAwal(database_path("seeders/berkas/hero/{$berkas}.webp"), Hero::FOLDER, Hero::DISK),
                'status_aktif' => false,
            ]);
        }
    }

    /**
     * @return array<string, array<string, mixed>> [nama berkas gambar => data hero]
     */
    private function daftarHero(): array
    {
        return [
            'pelatihan-it' => [
                'judul' => 'Pelatihan IT Praktis untuk Tim Anda',
                'deskripsi' => 'Kelas tatap muka dan daring yang disusun sesuai kebutuhan perusahaan, dibimbing instruktur berpengalaman industri.',
                'keyword' => ['Laravel', 'Flutter', 'UI/UX', 'Data Analyst'],
                'cta' => [
                    ['label' => 'Lihat Jadwal', 'url' => '#layanan', 'gaya' => GayaCta::Primary->value],
                    ['label' => 'Konsultasi', 'url' => '#kontak', 'gaya' => GayaCta::Secondary->value],
                ],
            ],
            'sertifikasi-it' => [
                'judul' => 'Sertifikasi IT Bertaraf Nasional',
                'deskripsi' => 'Persiapan dan uji kompetensi untuk membuktikan keahlian Anda dengan sertifikat yang diakui industri.',
                'keyword' => ['Junior Web Developer', 'Network Administrator', 'Digital Marketing'],
                'cta' => [
                    ['label' => 'Daftar Sertifikasi', 'url' => '#layanan', 'gaya' => GayaCta::Primary->value],
                ],
            ],
            'bootcamp' => [
                'judul' => 'Bootcamp Mahasiswa Siap Kerja',
                'deskripsi' => 'Program intensif berbasis proyek nyata agar mahasiswa siap masuk dunia kerja sebagai developer.',
                'keyword' => ['Fullstack Web', 'Mobile Developer', 'Proyek Nyata'],
                'cta' => [
                    ['label' => 'Gabung Bootcamp', 'url' => '#layanan', 'gaya' => GayaCta::Primary->value],
                    ['label' => 'Tanya via WhatsApp', 'url' => 'https://wa.me/6281234567890', 'gaya' => GayaCta::Secondary->value],
                ],
            ],
            'mobile-apps' => [
                'judul' => 'Aplikasi Mobile untuk Bisnis yang Bertumbuh',
                'deskripsi' => 'Kami merancang dan membangun aplikasi Android & iOS yang cepat, aman, dan mudah digunakan pelanggan Anda.',
                'keyword' => ['Android', 'iOS', 'Flutter'],
                'cta' => [
                    ['label' => 'Lihat Portofolio', 'url' => '#portofolio', 'gaya' => GayaCta::Primary->value],
                    ['label' => 'Hubungi Kami', 'url' => '#kontak', 'gaya' => GayaCta::Secondary->value],
                ],
            ],
        ];
    }
}
