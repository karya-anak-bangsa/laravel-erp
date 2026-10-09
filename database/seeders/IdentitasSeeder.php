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
            // Logo lebar berlatar transparan: tema Monochrome menghitamkannya dan footer memutihkannya lewat filter CSS.
            'logo_website' => $this->salinBerkasAwal(database_path('seeders/berkas/identitas/logo-tkab.webp'), Identitas::FOLDER, Identitas::DISK),
            'favicon_website' => $this->salinBerkasAwal(public_path('favicon.png'), Identitas::FOLDER, Identitas::DISK),
            'email' => 'info@karyaanakbangsa.co.id',
            'telepon' => '0812-3456-7890',
            'alamat' => 'Jl. Pipit 3 No 146, RT06 RW10, Depok Jaya, Pancoran Mas, Kota Depok 16432',
            'link_whatsapp' => 'https://wa.me/6281234567890',
        ]);
    }
}
