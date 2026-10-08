<?php

namespace Database\Seeders;

use App\Models\CompanyProfile\Portofolio;
use App\Services\CompanyProfile\PortofolioService;
use Database\Seeders\Concerns\MenyalinBerkasAwal;
use Illuminate\Database\Seeder;

class PortofolioDummySeeder extends Seeder
{
    use MenyalinBerkasAwal;

    /**
     * Portofolio contoh untuk mencoba daftar, pencarian, dan filter kategori.
     * Dijalankan otomatis hanya di lokal (lihat DatabaseSeeder). Gambar memakai
     * ilustrasi layanan agar tidak menambah berkas biner baru.
     */
    public function run(PortofolioService $portofolioService): void
    {
        foreach ($this->daftarPortofolio() as [$berkas, $judul, $kategori, $deskripsi]) {
            // Berdasarkan judul agar aman dijalankan ulang dan tidak menyalin gambar dua kali.
            if (Portofolio::withTrashed()->where('judul', $judul)->exists()) {
                continue;
            }

            Portofolio::create([
                'judul' => $judul,
                'slug' => $portofolioService->buatSlug($judul),
                'kategori' => $kategori,
                'deskripsi' => $deskripsi,
                'gambar' => $this->salinBerkasAwal(database_path("seeders/berkas/layanan/{$berkas}.webp"), Portofolio::FOLDER, Portofolio::DISK),
            ]);
        }
    }

    /**
     * @return list<array{string, string, string, string}> [berkas gambar, judul, kategori, deskripsi]
     */
    private function daftarPortofolio(): array
    {
        return [
            [
                'website',
                'Website Profil Sekolah Harapan Bangsa',
                'Website',
                'Website profil sekolah dengan halaman berita, galeri kegiatan, dan informasi penerimaan peserta didik baru. Dilengkapi panel admin agar guru dapat memperbarui konten sendiri.',
            ],
            [
                'website',
                'Toko Online Kerajinan Nusantara',
                'Website',
                'Toko online produk kerajinan dengan katalog, keranjang belanja, ongkos kirim otomatis, dan pembayaran melalui transfer bank maupun dompet digital.',
            ],
            [
                'mobile-apps',
                'Aplikasi Presensi Karyawan Berbasis Lokasi',
                'Mobile Apps',
                'Aplikasi Android dan iOS untuk presensi karyawan dengan validasi lokasi dan swafoto, terhubung ke dasbor HR untuk rekap kehadiran bulanan.',
            ],
            [
                'mobile-apps',
                'Aplikasi Antrean Klinik Sehat Sentosa',
                'Mobile Apps',
                'Pasien dapat mengambil nomor antrean dari rumah dan menerima notifikasi saat gilirannya hampir tiba, sehingga ruang tunggu klinik tidak lagi penuh.',
            ],
            [
                'pelatihan-it',
                'Pelatihan Laravel untuk Dinas Kominfo',
                'Pelatihan IT',
                'Pelatihan lima hari bagi tim pengembang dinas, mulai dari dasar Laravel hingga membangun aplikasi layanan publik lengkap dengan autentikasi dan laporan.',
            ],
            [
                'bootcamp',
                'Bootcamp Fullstack Web Angkatan 1',
                'Bootcamp',
                'Program tiga bulan bagi mahasiswa tingkat akhir. Peserta bekerja dalam tim membangun aplikasi nyata dan mempresentasikannya di depan perwakilan industri.',
            ],
        ];
    }
}
