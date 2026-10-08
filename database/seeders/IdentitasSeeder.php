<?php

namespace Database\Seeders;

use App\Models\CompanyProfile\Identitas;
use Illuminate\Database\Seeder;
use Illuminate\Http\File;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use RuntimeException;

class IdentitasSeeder extends Seeder
{
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
            'logo_website' => $this->salinBerkasAwal('apple-touch-icon.png'),
            'favicon_website' => $this->salinBerkasAwal('favicon.png'),
            // Dibiarkan kosong agar admin wajib melengkapinya saat pertama kali menyimpan.
            'email' => '',
            'telepon' => '',
            'alamat' => '',
        ]);
    }

    private function salinBerkasAwal(string $namaBerkas): string
    {
        $path = Storage::disk(Identitas::DISK)->putFileAs(
            Identitas::FOLDER,
            new File(public_path($namaBerkas)),
            Str::uuid().'.'.pathinfo($namaBerkas, PATHINFO_EXTENSION),
        );

        if ($path === false) {
            throw new RuntimeException("Berkas awal {$namaBerkas} gagal disalin.");
        }

        return $path;
    }
}
