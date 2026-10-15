<?php

namespace Database\Seeders;

use App\Enums\CompanyProfile\StatusPublikasi;
use App\Models\CompanyProfile\Artikel;
use App\Models\CompanyProfile\KategoriArtikel;
use App\Services\CompanyProfile\ArtikelService;
use App\Services\CompanyProfile\KategoriArtikelService;
use App\Services\Shared\HtmlSanitizerService;
use Database\Seeders\Concerns\MenyalinBerkasAwal;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;
use RuntimeException;

class ArtikelDummySeeder extends Seeder
{
    use MenyalinBerkasAwal;

    /**
     * Artikel contoh yang terlihat nyata untuk mensimulasikan halaman artikel (pilihan pemilik 2026-10-16): 30 terbit
     * (3 per kategori) dan 2 draf untuk mencoba filter status. Bagian dari CompanyProfileSeeder (lokal & produksi),
     * setelah KategoriArtikelSeeder.
     * Isi tiap artikel ada di database/seeders/data/artikel/<slug judul>.html. Gambarnya ilustrasi sementara per
     * kategori (database/seeders/berkas/artikel/<slug kategori>.webp) sampai ilustrasi dari animator tersedia; tiap
     * artikel tetap mendapat salinan berkasnya sendiri.
     */
    public function run(ArtikelService $artikelService, KategoriArtikelService $kategoriArtikelService, HtmlSanitizerService $sanitizer): void
    {
        foreach ($this->daftarArtikel() as [$tanggal, $kategori, $judul, $status]) {
            // Berdasarkan judul agar aman dijalankan ulang dan tidak menyalin gambar dua kali.
            if (Artikel::withTrashed()->where('judul', $judul)->exists()) {
                continue;
            }

            // Kategori awal dibuat KategoriArtikelSeeder; dibuat di sini hanya bila tidak ada (mis. namanya diubah admin).
            $kategoriArtikel = KategoriArtikel::query()->firstWhere('nama_kategori', $kategori)
                ?? $kategoriArtikelService->simpan(['nama_kategori' => $kategori]);

            Artikel::create([
                'id_kategori_artikel' => $kategoriArtikel->id_kategori_artikel,
                'judul' => $judul,
                'slug' => $artikelService->buatSlug($judul),
                'deskripsi' => $this->isi(Str::slug($judul), $sanitizer),
                'gambar' => $this->salinBerkasAwal(database_path('seeders/berkas/artikel/'.Str::slug($kategori).'.webp'), Artikel::FOLDER, Artikel::DISK),
                'tanggal' => $tanggal,
                'status_publikasi' => $status,
            ]);
        }
    }

    /**
     * Isi artikel ditulis satu elemen blok per baris (p, h2, h3, ul/ol dengan li berisi p, seperti keluaran editor),
     * lalu disaring HtmlSanitizerService seperti isian form agar yang tersimpan sama dengan simpan lewat panel admin.
     */
    private function isi(string $slug, HtmlSanitizerService $sanitizer): string
    {
        $berkas = database_path("seeders/data/artikel/{$slug}.html");
        $baris = is_file($berkas) ? file($berkas, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES) : false;

        if ($baris === false) {
            throw new RuntimeException("Isi artikel {$berkas} tidak ditemukan.");
        }

        return (string) $sanitizer->bersihkan(implode('', array_map('trim', $baris)), judulBagian: true);
    }

    /**
     * Urut dari yang terlama agar ID mengikuti waktu terbit. Tanggal ditulis tetap agar hasil seed di lokal dan
     * produksi identik kapan pun dijalankan; artikel berita bertanggal sesuai kejadiannya.
     *
     * @return list<array{string, string, string, StatusPublikasi}> [tanggal Y-m-d, kategori, judul, status]
     */
    private function daftarArtikel(): array
    {
        $terbit = StatusPublikasi::Terbit;
        $draf = StatusPublikasi::Draf;

        return [
            ['2026-07-02', 'Database', 'Backup Database MySQL Otomatis, Kebiasaan Kecil yang Menyelamatkan Data', $terbit],
            ['2026-07-07', 'Python', 'Belajar Python dari Nol: Instalasi, Virtual Environment, dan Program Pertama', $terbit],
            ['2026-07-09', 'Laptop Thin and Light', 'Intel, AMD, atau Snapdragon: Prosesor Mana yang Pas untuk Laptop Tipis?', $terbit],
            ['2026-07-14', 'JavaScript', 'Perbedaan var, let, dan const di JavaScript dan Kapan Memakainya', $terbit],
            ['2026-07-16', 'Laptop Gaming', 'Laptop Gaming untuk Kuliah dan Coding, Pilihan Tepat atau Berlebihan?', $terbit],
            ['2026-07-21', 'Sistem Operasi', 'Cara Membuat Bootable USB Windows 11 dengan Rufus dan Media Creation Tool', $terbit],
            ['2026-07-24', 'Keamanan Siber', 'Cara Menjaga Keamanan Website Perusahaan', $terbit],
            ['2026-07-28', 'Laravel', 'Cara Install Laravel 13 di Windows dengan Laragon', $terbit],
            ['2026-07-31', 'Kecerdasan Buatan', 'Mengenal RAG, Cara Chatbot Menjawab dari Dokumen Perusahaan', $terbit],
            ['2026-08-05', 'Flutter', 'Mengenal Stateless dan Stateful Widget di Flutter', $terbit],
            ['2026-08-08', 'Keamanan Siber', 'Passkey, Pengganti Password yang Lebih Aman dan Cara Mengaktifkannya', $terbit],
            ['2026-08-12', 'Laptop Thin and Light', 'Copilot+ PC dan NPU: Perlukah Dipertimbangkan Saat Membeli Laptop Baru?', $terbit],
            ['2026-08-20', 'JavaScript', 'Memahami Async/Await di JavaScript lewat Contoh Sehari-hari', $terbit],
            ['2026-08-25', 'Python', 'Mengolah Data Excel dengan Python dan pandas untuk Pekerjaan Kantor', $terbit],
            ['2026-08-28', 'Sistem Operasi', 'Windows 11 26H2 Resmi Masuk Release Preview, Begini Cara Mencobanya', $terbit],
            ['2026-08-31', 'Database', 'Mengenal Indeks di MySQL agar Query Tidak Lambat', $terbit],
            ['2026-09-03', 'Laptop Gaming', '7 Cara Merawat Laptop Gaming agar Tidak Cepat Panas', $terbit],
            ['2026-09-07', 'Kecerdasan Buatan', 'Coding dengan Bantuan AI: Lebih Cepat tanpa Kehilangan Pemahaman', $terbit],
            ['2026-09-10', 'Laravel', 'Eloquent Relationship di Laravel dan Cara Menghindari N+1 Query', $terbit],
            ['2026-09-14', 'Flutter', 'Langkah Merilis Aplikasi Flutter ke Google Play Store', $terbit],
            ['2026-09-17', 'Python', 'Membuat Bot Telegram Sederhana dengan Python', $terbit],
            ['2026-09-21', 'Keamanan Siber', 'Cara Mengenali Email dan Pesan WhatsApp Phishing sebelum Terlambat', $terbit],
            ['2026-09-24', 'JavaScript', 'Mengenal TypeScript: Kapan Proyek JavaScript Perlu Beralih?', $terbit],
            ['2026-09-30', 'Laptop Thin and Light', 'Spesifikasi Minimal Laptop Thin and Light untuk Mahasiswa dan Pekerja Kantoran', $terbit],
            ['2026-10-02', 'Flutter', 'Flutter vs React Native: Mana yang Cocok untuk Aplikasi Bisnis Anda?', $terbit],
            ['2026-10-04', 'Database', 'MySQL vs PostgreSQL: Mana yang Cocok untuk Aplikasi Anda?', $terbit],
            ['2026-10-06', 'Laravel', 'Validasi Form dengan Form Request agar Controller Laravel Tetap Ramping', $terbit],
            ['2026-10-09', 'Laptop Gaming', 'Cara Memilih Laptop Gaming: Jangan Hanya Lihat Nama GPU, Cek TGP-nya', $terbit],
            ['2026-10-12', 'Kecerdasan Buatan', 'Prompt Engineering untuk Pemula: Cara Bertanya ke Chatbot AI agar Jawabannya Tepat', $terbit],
            ['2026-10-13', 'Laravel', 'Membuat REST API dengan Laravel Sanctum', $draf],
            ['2026-10-14', 'Sistem Operasi', 'ESU Windows 10 untuk Pengguna Rumahan Diperpanjang hingga Oktober 2027, Ini Pilihan untuk PC Lama', $terbit],
            ['2026-10-15', 'Sistem Operasi', 'Ubuntu 26.04 LTS: Fitur Baru dan Cara Upgrade dari 24.04', $draf],
        ];
    }
}
