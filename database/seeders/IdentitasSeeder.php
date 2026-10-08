<?php

namespace Database\Seeders;

use App\Models\CompanyProfile\Identitas;
use Database\Seeders\Concerns\MenyalinBerkasAwal;
use Illuminate\Database\Seeder;

class IdentitasSeeder extends Seeder
{
    use MenyalinBerkasAwal;

    /**
     * Membuat baris identitas tunggal; data lengkapnya diisi admin lewat panel.
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
            // Kolom logo & favicon wajib berisi, jadi ikon bawaan aplikasi dipakai sebagai awal.
            'logo_website' => $this->salinBerkasAwal(public_path('apple-touch-icon.png'), Identitas::FOLDER, Identitas::DISK),
            'favicon_website' => $this->salinBerkasAwal(public_path('favicon.png'), Identitas::FOLDER, Identitas::DISK),
            // Dibiarkan kosong agar admin wajib melengkapinya saat pertama kali menyimpan.
            'email' => '',
            'telepon' => '',
            'alamat' => '',
        ]);
    }
}
