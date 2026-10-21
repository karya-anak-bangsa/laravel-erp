<?php

namespace Database\Seeders;

use App\Models\CompanyProfile\Identitas;
use Database\Seeders\Concerns\MenyalinBerkasAwal;
use Illuminate\Database\Seeder;

class IdentitasSeeder extends Seeder
{
    use MenyalinBerkasAwal;

    /**
     * Membuat baris identitas tunggal berisi data perusahaan; bisa diubah admin lewat panel.
     */
    public function run(): void
    {
        // Termasuk yang terhapus agar seeder aman dijalankan ulang tanpa menimpa isian admin.
        if (Identitas::withTrashed()->exists()) {
            return;
        }

        Identitas::create([
            'nama_perusahaan' => 'PT. Teknologi Karya Anak Bangsa',
            'judul_website' => 'PT. Teknologi Karya Anak Bangsa',
            'alamat_website' => 'https://karyaanakbangsa.co.id',
            // Ringkasan situs untuk hasil pencarian dan footer semua halaman; teksnya sama dengan deskripsi hero aktif.
            'meta_deskripsi' => 'Kami menghadirkan solusi digital modern yang membantu bisnis berkembang lebih cepat, efisien, dan siap menghadapi era teknologi.',
            // Logo lebar berlatar transparan: tema Monochrome menghitamkannya dan footer memutihkannya lewat filter CSS.
            'logo_website' => $this->salinBerkasAwal(database_path('seeders/berkas/identitas/logo-tkab.webp'), Identitas::FOLDER, Identitas::DISK),
            'favicon_website' => $this->salinBerkasAwal(public_path('favicon.png'), Identitas::FOLDER, Identitas::DISK),
            'email' => ['aryajayaalamsyah@gmail.com'],
            'telepon' => ['0819-3285-5946', '0811-888-475'],
            'alamat' => 'Silah Hills, Ciganjur, Jagakarsa, Jakarta Selatan - 12630',
            // Juga dipakai tombol Hubungi Kami di HeroSeeder.
            'link_whatsapp' => 'https://wa.me/6281932855946',
        ]);
    }
}
