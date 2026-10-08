<?php

namespace Database\Seeders;

use App\Models\CompanyProfile\Layanan;
use App\Support\TeksHtml;
use Database\Seeders\Concerns\MenyalinBerkasAwal;
use Illuminate\Database\Seeder;

class LayananSeeder extends Seeder
{
    use MenyalinBerkasAwal;

    /**
     * Lima layanan perusahaan beserta gambar ilustrasi; isinya bisa diubah admin lewat panel.
     */
    public function run(): void
    {
        // Termasuk yang terhapus agar seeder aman dijalankan ulang tanpa menimpa isian admin.
        if (Layanan::withTrashed()->exists()) {
            return;
        }

        foreach ($this->daftarLayanan() as $urutan => [$berkas, $judul, $deskripsi, $keterangan]) {
            Layanan::create([
                'judul' => $judul,
                'deskripsi' => TeksHtml::dariTeksPolos($deskripsi),
                'keterangan' => TeksHtml::dariTeksPolos($keterangan),
                'gambar' => $this->salinBerkasAwal(database_path("seeders/berkas/layanan/{$berkas}.webp"), Layanan::FOLDER, Layanan::DISK),
                'urutan_ke' => $urutan + 1,
            ]);
        }
    }

    /**
     * @return list<array{string, string, string, string}> [berkas gambar, judul, deskripsi, keterangan]
     */
    private function daftarLayanan(): array
    {
        return [
            [
                'website',
                'Pembuatan Website',
                'Website company profile, toko online, hingga sistem informasi yang cepat, aman, dan mudah dikelola.',
                'Kami menangani seluruh proses, mulai dari analisis kebutuhan, desain tampilan, pengembangan, hingga pemasangan di server. Setiap website dibuat responsif, ramah mesin pencari, dan dilengkapi panel admin agar konten mudah diperbarui sendiri.',
            ],
            [
                'mobile-apps',
                'Pembuatan Mobile Apps',
                'Aplikasi Android dan iOS sesuai kebutuhan bisnis, dari desain hingga rilis ke Play Store dan App Store.',
                'Aplikasi dirancang dengan antarmuka yang mudah digunakan dan performa yang ringan. Kami juga menyiapkan integrasi dengan sistem yang sudah ada serta pendampingan setelah aplikasi dirilis.',
            ],
            [
                'pelatihan-it',
                'Pelatihan IT',
                'Pelatihan pemrograman, desain, dan teknologi terkini untuk perusahaan, instansi, maupun perorangan.',
                'Materi disusun sesuai kebutuhan peserta dan dibawakan oleh instruktur berpengalaman di industri. Pelatihan dapat diselenggarakan secara tatap muka maupun daring, lengkap dengan studi kasus dan praktik langsung.',
            ],
            [
                'sertifikasi-it',
                'Sertifikasi IT',
                'Persiapan dan pelaksanaan uji kompetensi untuk mendapatkan sertifikat keahlian IT yang diakui industri.',
                'Peserta dibekali materi persiapan, simulasi uji, dan pendampingan hingga pelaksanaan sertifikasi. Sertifikat menjadi bukti kompetensi yang meningkatkan daya saing di dunia kerja.',
            ],
            [
                'bootcamp',
                'Bootcamp Mahasiswa',
                'Program intensif berbasis proyek nyata yang menyiapkan mahasiswa menjadi talenta IT siap kerja.',
                'Selama bootcamp, mahasiswa belajar dalam tim, mengerjakan proyek seperti di dunia kerja, dan mendapat bimbingan mentor. Di akhir program, peserta memiliki portofolio yang dapat ditunjukkan kepada calon pemberi kerja.',
            ],
        ];
    }
}
