<?php

namespace Database\Seeders;

use App\Enums\CompanyProfile\StatusPublikasi;
use App\Models\CompanyProfile\Artikel;
use App\Models\CompanyProfile\KategoriArtikel;
use App\Services\CompanyProfile\ArtikelService;
use App\Support\TeksHtml;
use Database\Seeders\Concerns\MenyalinBerkasAwal;
use Illuminate\Database\Seeder;

class ArtikelDummySeeder extends Seeder
{
    use MenyalinBerkasAwal;

    /**
     * Artikel contoh untuk mencoba daftar, filter kategori & status, dan sub-judul isi.
     * Dijalankan otomatis hanya di lokal (lihat DatabaseSeeder), setelah KategoriArtikelSeeder.
     * Gambar memakai ilustrasi layanan agar tidak menambah berkas biner baru.
     */
    public function run(ArtikelService $artikelService): void
    {
        foreach ($this->daftarArtikel() as $hariLalu => [$berkas, $kategori, $judul, $status, $bagian]) {
            // Berdasarkan judul agar aman dijalankan ulang dan tidak menyalin gambar dua kali.
            if (Artikel::withTrashed()->where('judul', $judul)->exists()) {
                continue;
            }

            Artikel::create([
                'id_kategori_artikel' => KategoriArtikel::firstOrCreate(['nama_kategori' => $kategori])->id_kategori_artikel,
                'judul' => $judul,
                'slug' => $artikelService->buatSlug($judul),
                'deskripsi' => $this->isi($bagian),
                'gambar' => $this->salinBerkasAwal(database_path("seeders/berkas/layanan/{$berkas}.webp"), Artikel::FOLDER, Artikel::DISK),
                'tanggal' => today()->subDays($hariLalu * 6),
                'status_publikasi' => $status,
            ]);
        }
    }

    /**
     * @param  array<string, string>  $bagian  [sub-judul => paragraf]; kunci kosong = paragraf pembuka
     */
    private function isi(array $bagian): string
    {
        $html = '';

        foreach ($bagian as $subJudul => $paragraf) {
            if ($subJudul !== '') {
                $html .= '<h2>'.e($subJudul).'</h2>';
            }

            $html .= TeksHtml::dariTeksPolos($paragraf);
        }

        return $html;
    }

    /**
     * @return list<array{string, string, string, StatusPublikasi, array<string, string>}>
     *                                                                                     [berkas gambar, kategori, judul, status, isi per bagian]
     */
    private function daftarArtikel(): array
    {
        return [
            ['website', 'Tips & Tutorial', '5 Alasan Bisnis Kecil Perlu Website Sendiri', StatusPublikasi::Terbit, [
                '' => 'Media sosial memang praktis, tetapi website memberi bisnis Anda rumah sendiri di internet yang sepenuhnya Anda kendalikan.',
                'Dipercaya calon pelanggan' => 'Pelanggan cenderung lebih yakin bertransaksi dengan bisnis yang memiliki website resmi lengkap dengan alamat dan kontak yang jelas.',
                'Mudah ditemukan di Google' => 'Website yang dioptimalkan untuk mesin pencari membantu calon pelanggan menemukan produk Anda saat mereka sedang mencarinya.',
            ]],
            ['mobile-apps', 'Teknologi', 'Mengenal Perbedaan Aplikasi Native dan Hybrid', StatusPublikasi::Terbit, [
                '' => 'Sebelum membuat aplikasi mobile, penting memahami pilihan teknologi yang akan memengaruhi biaya, performa, dan waktu pengerjaan.',
                'Aplikasi native' => 'Dibangun khusus untuk satu platform sehingga performanya optimal dan dapat memakai seluruh fitur perangkat.',
                'Aplikasi hybrid' => 'Satu kode untuk Android dan iOS sekaligus, sehingga pengembangan lebih cepat dan biaya lebih hemat.',
            ]],
            ['pelatihan-it', 'Pelatihan & Sertifikasi', 'Tips Memilih Pelatihan IT yang Tepat untuk Tim Anda', StatusPublikasi::Terbit, [
                '' => 'Pelatihan yang tepat sasaran membuat investasi pengembangan SDM terasa hasilnya dalam pekerjaan sehari-hari.',
                'Petakan kebutuhan lebih dulu' => 'Tentukan keterampilan yang paling dibutuhkan tim, lalu pilih materi yang langsung bisa dipraktikkan.',
            ]],
            ['sertifikasi-it', 'Pelatihan & Sertifikasi', 'Manfaat Sertifikasi Kompetensi bagi Lulusan Baru', StatusPublikasi::Draf, [
                '' => 'Sertifikat kompetensi menjadi bukti keahlian yang diakui industri dan membantu lulusan baru menonjol saat melamar kerja.',
            ]],
            ['bootcamp', 'Kabar Perusahaan', 'Bootcamp Fullstack Web Angkatan Pertama Resmi Dibuka', StatusPublikasi::Terbit, [
                '' => 'Program bootcamp intensif selama tiga bulan bagi mahasiswa tingkat akhir kini resmi membuka pendaftaran.',
                'Belajar lewat proyek nyata' => 'Peserta bekerja dalam tim, dibimbing mentor, dan mempresentasikan hasil proyeknya di akhir program.',
            ]],
            ['website', 'Tips & Tutorial', 'Cara Menjaga Keamanan Website Perusahaan', StatusPublikasi::Draf, [
                '' => 'Keamanan website bukan pekerjaan sekali jadi; ada beberapa kebiasaan sederhana yang membuat website jauh lebih aman.',
            ]],
        ];
    }
}
