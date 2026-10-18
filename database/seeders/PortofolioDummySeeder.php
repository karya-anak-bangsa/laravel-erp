<?php

namespace Database\Seeders;

use App\Models\CompanyProfile\Portofolio;
use App\Services\CompanyProfile\PortofolioService;
use App\Support\TeksHtml;
use Database\Seeders\Concerns\MenyalinBerkasAwal;
use Illuminate\Database\Seeder;

class PortofolioDummySeeder extends Seeder
{
    use MenyalinBerkasAwal;

    /**
     * Portofolio contoh untuk mencoba daftar, pencarian, filter kategori, dan carousel detail.
     * Bagian dari CompanyProfileSeeder (lokal & produksi). Gambar utama memakai ilustrasi layanan dan
     * gambar tambahan memakai ilustrasi hero & artikel, agar tidak menambah berkas biner baru.
     */
    public function run(PortofolioService $portofolioService): void
    {
        foreach ($this->daftarPortofolio() as [$berkas, $judul, $kategori, $deskripsi, $galeri]) {
            // Berdasarkan judul agar aman dijalankan ulang dan tidak menyalin gambar dua kali.
            if (Portofolio::withTrashed()->where('judul', $judul)->exists()) {
                continue;
            }

            $portofolio = Portofolio::create([
                'judul' => $judul,
                'slug' => $portofolioService->buatSlug($judul),
                'kategori' => $kategori,
                'deskripsi' => TeksHtml::dariTeksPolos($deskripsi),
                'gambar' => $this->salinBerkasAwal(database_path("seeders/berkas/layanan/{$berkas}.webp"), Portofolio::FOLDER, Portofolio::DISK),
            ]);

            foreach ($galeri as $berkasGaleri) {
                $portofolio->gambarPortofolio()->create([
                    'gambar' => $this->salinBerkasAwal(database_path("seeders/berkas/{$berkasGaleri}.webp"), Portofolio::FOLDER, Portofolio::DISK),
                ]);
            }
        }
    }

    /**
     * Setiap portofolio contoh berisi gambar penuh: 1 gambar utama + 4 gambar tambahan
     * (StorePortofolioRequest::MAKS_GAMBAR, pilihan pemilik 2026-10-18).
     *
     * @return list<array{string, string, string, string, list<string>}> [berkas gambar utama, judul, kategori, deskripsi, gambar tambahan]
     */
    private function daftarPortofolio(): array
    {
        return [
            [
                'website',
                'Website Profil Sekolah Harapan Bangsa',
                'Website',
                'Website profil sekolah dengan halaman berita, galeri kegiatan, dan informasi penerimaan peserta didik baru. Dilengkapi panel admin agar guru dapat memperbarui konten sendiri.',
                ['artikel/laravel', 'artikel/javascript', 'artikel/database', 'artikel/keamanan-siber'],
            ],
            [
                'website',
                'Toko Online Kerajinan Nusantara',
                'Website',
                'Toko online produk kerajinan dengan katalog, keranjang belanja, ongkos kirim otomatis, dan pembayaran melalui transfer bank maupun dompet digital.',
                ['artikel/javascript', 'artikel/database', 'artikel/keamanan-siber', 'artikel/laravel'],
            ],
            [
                'mobile-apps',
                'Aplikasi Presensi Karyawan Berbasis Lokasi',
                'Mobile Apps',
                'Aplikasi Android dan iOS untuk presensi karyawan dengan validasi lokasi dan swafoto, terhubung ke dasbor HR untuk rekap kehadiran bulanan.',
                ['hero/mobile-apps', 'artikel/flutter', 'artikel/database', 'artikel/keamanan-siber'],
            ],
            [
                'mobile-apps',
                'Aplikasi Antrean Klinik Sehat Sentosa',
                'Mobile Apps',
                'Pasien dapat mengambil nomor antrean dari rumah dan menerima notifikasi saat gilirannya hampir tiba, sehingga ruang tunggu klinik tidak lagi penuh.',
                ['hero/mobile-apps', 'artikel/flutter', 'artikel/javascript', 'artikel/database'],
            ],
            [
                'pelatihan-it',
                'Pelatihan Laravel untuk Dinas Kominfo',
                'Pelatihan IT',
                'Pelatihan lima hari bagi tim pengembang dinas, mulai dari dasar Laravel hingga membangun aplikasi layanan publik lengkap dengan autentikasi dan laporan.',
                ['hero/pelatihan-it', 'artikel/laravel', 'artikel/database', 'artikel/keamanan-siber'],
            ],
            [
                'bootcamp',
                'Bootcamp Fullstack Web Angkatan 1',
                'Bootcamp',
                'Program tiga bulan bagi mahasiswa tingkat akhir. Peserta bekerja dalam tim membangun aplikasi nyata dan mempresentasikannya di depan perwakilan industri.',
                ['hero/bootcamp', 'artikel/javascript', 'artikel/laravel', 'artikel/kecerdasan-buatan'],
            ],
            // Ditambahkan 2026-10-11 agar tab Sertifikasi IT di beranda berisi; tetap di akhir agar urutan id lama tidak bergeser.
            [
                'sertifikasi-it',
                'Sertifikasi Junior Web Developer Lulusan SMK',
                'Sertifikasi IT',
                'Pendampingan uji kompetensi bagi lulusan SMK, mulai dari pembekalan materi dan simulasi uji hingga pelaksanaan asesmen, sehingga peserta memperoleh sertifikat keahlian yang diakui industri.',
                ['hero/sertifikasi-it', 'artikel/javascript', 'artikel/laravel', 'artikel/database'],
            ],
        ];
    }
}
