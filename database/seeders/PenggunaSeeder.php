<?php

namespace Database\Seeders;

use App\Models\Pengguna;
use Illuminate\Database\Seeder;
use RuntimeException;

class PenggunaSeeder extends Seeder
{
    /**
     * Membuat akun admin awal dari ADMIN_NAMA, ADMIN_EMAIL, ADMIN_PASSWORD.
     */
    public function run(): void
    {
        $admin = config('auth.admin_awal');

        if (blank($admin['nama']) || blank($admin['email']) || blank($admin['password'])) {
            throw new RuntimeException('ADMIN_NAMA, ADMIN_EMAIL, dan ADMIN_PASSWORD wajib diisi di .env.');
        }

        // firstOrCreate agar seeder aman dijalankan ulang tanpa menimpa password yang sudah diganti.
        Pengguna::withTrashed()->firstOrCreate(
            ['email' => $admin['email']],
            ['nama' => $admin['nama'], 'password' => $admin['password']],
        );
    }
}
