<?php

namespace Database\Seeders;

use App\Enums\CompanyProfile\StatusPublikasi;
use App\Models\CompanyProfile\KategoriTutorial;
use App\Models\CompanyProfile\MateriTutorial;
use App\Models\CompanyProfile\Tutorial;
use App\Services\CompanyProfile\MateriTutorialService;
use App\Services\CompanyProfile\TutorialService;
use App\Services\Shared\HtmlSanitizerService;
use App\Support\TeksHtml;
use Database\Seeders\Concerns\MenyalinBerkasAwal;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;
use RuntimeException;

class TutorialDummySeeder extends Seeder
{
    use MenyalinBerkasAwal;

    /**
     * Tutorial contoh (pilihan pemilik 2026-10-20): dua tutorial per kategori, masing-masing tepat 4 materi bersambung.
     * Bagian dari CompanyProfileSeeder (lokal & produksi).
     * Isi tiap materi ada di database/seeders/data/tutorial/<slug judul tutorial>/<slug judul materi>.html. Sampulnya
     * memakai ulang ilustrasi artikel per kategori (database/seeders/berkas/<berkas>.webp) agar tidak menambah berkas
     * biner baru.
     */
    public function run(TutorialService $tutorialService, MateriTutorialService $materiTutorialService, HtmlSanitizerService $sanitizer): void
    {
        // Kategori dicocokkan lewat nama dari KategoriTutorialSeeder, yang wajib dijalankan lebih dulu.
        $idKategori = KategoriTutorial::query()->pluck('id_kategori_tutorial', 'nama_kategori');

        // Dibalik karena daftar publik menampilkan tutorial yang terakhir dibuat lebih dulu.
        foreach (array_reverse($this->daftarTutorial()) as [$kategori, $judul, $berkasSampul, $deskripsi, $daftarMateri]) {
            // Berdasarkan judul agar aman dijalankan ulang dan tidak menyalin gambar dua kali.
            if (Tutorial::withTrashed()->where('judul', $judul)->exists()) {
                continue;
            }

            $tutorial = Tutorial::create([
                'id_kategori_tutorial' => $idKategori[$kategori],
                'judul' => $judul,
                'slug' => $tutorialService->buatSlug($judul),
                'deskripsi' => TeksHtml::dariTeksPolos($deskripsi),
                'gambar' => $this->salinBerkasAwal(database_path("seeders/berkas/{$berkasSampul}.webp"), Tutorial::FOLDER, Tutorial::DISK),
                'status_publikasi' => StatusPublikasi::Terbit,
            ]);

            foreach ($daftarMateri as $urutan => $judulMateri) {
                MateriTutorial::create([
                    'id_tutorial' => $tutorial->id_tutorial,
                    'judul' => $judulMateri,
                    'slug' => $materiTutorialService->buatSlug($judulMateri, $tutorial->id_tutorial),
                    'deskripsi' => $this->isi(Str::slug($judul).'/'.Str::slug($judulMateri), $sanitizer),
                    'urutan_ke' => $urutan + 1,
                ]);
            }
        }
    }

    /**
     * Isi materi ditulis seperti isi artikel (satu elemen blok per baris), ditambah code dan blok <pre><code>. Baris di
     * luar blok kode dirapatkan seperti keluaran editor; isi <pre> dipertahankan apa adanya karena spasi dan baris
     * barunya bagian dari kode. Hasilnya disaring HtmlSanitizerService seperti isian form.
     */
    private function isi(string $jalur, HtmlSanitizerService $sanitizer): string
    {
        $berkas = database_path("seeders/data/tutorial/{$jalur}.html");
        $html = is_file($berkas) ? file_get_contents($berkas) : false;

        if ($html === false) {
            throw new RuntimeException("Isi materi {$berkas} tidak ditemukan.");
        }

        $bagian = preg_split('#(<pre>.*?</pre>)#s', $html, -1, PREG_SPLIT_DELIM_CAPTURE | PREG_SPLIT_NO_EMPTY) ?: [];
        $rapat = array_map(fn (string $teks): string => str_starts_with($teks, '<pre>')
            ? $teks
            : implode('', array_map('trim', preg_split('/\R/', $teks) ?: [])), $bagian);

        return (string) $sanitizer->bersihkan(implode('', $rapat), judulBagian: true, kode: true);
    }

    /**
     * Sesuai urutan tampil di daftar publik: tutorial dasar setiap kategori lebih dulu, lalu tutorial lanjutannya.
     *
     * @return list<array{string, string, string, string, list<string>}> [kategori, judul, berkas sampul, deskripsi teks polos,
     *                                                                   judul materi urut]
     */
    private function daftarTutorial(): array
    {
        return [
            [
                'Laravel',
                'Dasar-Dasar Laravel 13',
                'artikel/laravel',
                "Kenali fitur baru Laravel 13 sambil menyiapkan dasar aplikasi data buku, mulai dari memasang proyek hingga membuat rute, tampilan Blade, dan model Eloquent.\n\n"
                .'Materi disusun berurutan sehingga sebaiknya dibaca dari materi pertama. Anda cukup memahami dasar PHP dan HTML. Contoh perintahnya memakai Laragon di Windows, tetapi langkahnya juga berlaku di sistem operasi lain.',
                [
                    'Fitur-Fitur Baru di Laravel 13',
                    'Instalasi dan Konfigurasi Laravel 13',
                    'Routing, Controller, dan Blade',
                    'Migrasi dan Model Eloquent',
                ],
            ],
            [
                'JavaScript',
                'Dasar-Dasar JavaScript Modern',
                'artikel/javascript',
                "Pelajari dasar JavaScript modern lewat contoh toko buku, mulai dari variabel, tipe data, dan operator hingga percabangan, perulangan, fungsi, dan objek.\n\n"
                .'Setiap contoh bisa langsung dicoba di konsol peramban atau Node.js. Anda tidak memerlukan pengalaman pemrograman sebelumnya; cukup siapkan peramban dan editor teks.',
                [
                    'Variabel, Tipe Data, dan Operator',
                    'Percabangan dan Perulangan',
                    'Fungsi dan Arrow Function',
                    'Array dan Objek',
                ],
            ],
            [
                'TypeScript',
                'Dasar-Dasar TypeScript',
                'artikel/sistem-operasi',
                "Tambahkan pemeriksaan tipe ke kode JavaScript dengan TypeScript, mulai dari memasang kompilator dan menulis anotasi tipe hingga menulis fungsi yang aman dari salah tipe.\n\n"
                .'Contohnya memakai data toko daring dan disusun berurutan sehingga sebaiknya dibaca dari materi pertama. Anda cukup memahami dasar JavaScript dan sudah memasang Node.js.',
                [
                    'Mengenal dan Memasang TypeScript',
                    'Tipe Data Dasar dan Anotasi Tipe',
                    'Interface dan Type Alias',
                    'Fungsi, Union Type, dan Narrowing',
                ],
            ],
            [
                'Machine Learning',
                'Pengantar Machine Learning dengan Python',
                'artikel/python',
                "Pahami cara komputer belajar dari data, lalu siapkan Python, JupyterLab, dan scikit-learn untuk mengenali isi data dan membaginya menjadi data latih dan data uji.\n\n"
                .'Materi disusun berurutan sehingga sebaiknya dibaca dari materi pertama. Anda cukup memahami dasar Python; semua contohnya memakai data bunga iris bawaan scikit-learn.',
                [
                    'Mengenal Machine Learning',
                    'Menyiapkan Python, Jupyter, dan scikit-learn',
                    'Mengenal Data dengan pandas',
                    'Membagi Data Latih dan Data Uji',
                ],
            ],
            [
                'Deep Learning',
                'Pengantar Deep Learning dengan Keras',
                'artikel/kecerdasan-buatan',
                "Selami cara kerja jaringan saraf tiruan, lalu bangun dan latih model pengenal angka tulisan tangan dengan TensorFlow dan Keras tanpa membuatnya sekadar menghafal data.\n\n"
                .'Materi disusun berurutan sehingga sebaiknya dibaca dari materi pertama. Anda cukup memahami dasar Python. Komputer tanpa kartu grafis pun bisa dipakai, atau gunakan Google Colab.',
                [
                    'Mengenal Jaringan Saraf Tiruan',
                    'Menyiapkan TensorFlow dan Keras',
                    'Membangun Jaringan Saraf Pertama',
                    'Melatih Model dan Mengatasi Overfitting',
                ],
            ],
            [
                'Laravel',
                'Membangun CRUD dengan Laravel 13',
                'artikel/laravel',
                "Lengkapi aplikasi perpustakaan Laravel 13 dengan fitur CRUD, mulai dari menampilkan dan mencari data buku hingga menambah, mengubah, dan menghapusnya dengan aman.\n\n"
                .'Tutorial ini melanjutkan proyek dari tutorial "Dasar-Dasar Laravel 13", jadi selesaikan tutorial itu lebih dulu. Materi disusun berurutan sehingga sebaiknya dibaca dari materi pertama.',
                [
                    'Menampilkan Data dari Basis Data',
                    'Menambah Data dengan Formulir dan Validasi',
                    'Mengubah Data di Basis Data',
                    'Menghapus Data dari Basis Data',
                ],
            ],
            [
                'JavaScript',
                'Membuat Aplikasi Daftar Tugas dengan JavaScript',
                'artikel/javascript',
                "Bangun aplikasi daftar tugas dengan JavaScript murni, mulai dari mengubah elemen halaman dan menangani klik hingga menyimpan tugas di peramban dan mengambil data dari API.\n\n"
                .'Aplikasinya hanya terdiri atas satu berkas HTML dan satu berkas JavaScript. Anda cukup memahami dasar HTML dan dasar JavaScript seperti yang dibahas di tutorial "Dasar-Dasar JavaScript Modern".',
                [
                    'Memilih dan Mengubah Elemen DOM',
                    'Menangani Event',
                    'Menyimpan Data di localStorage',
                    'Mengambil Data dari API dengan Fetch',
                ],
            ],
            [
                'TypeScript',
                'Menerapkan TypeScript di Proyek Vite',
                'artikel/sistem-operasi',
                "Terapkan TypeScript di proyek Vite sambil membangun katalog pengguna, mulai dari pengaturan kompilator hingga kode yang dapat dipakai ulang dan pemeriksaan data dari API.\n\n"
                .'Materi disusun berurutan sehingga sebaiknya dibaca dari materi pertama. Anda cukup memahami dasar JavaScript, HTML, dan isi tutorial "Dasar-Dasar TypeScript".',
                [
                    'Menyiapkan Proyek Vite dan tsconfig',
                    'Generic',
                    'Utility Types',
                    'Memberi Tipe pada Data dari API',
                ],
            ],
            [
                'Machine Learning',
                'Membangun Model Prediksi dengan scikit-learn',
                'artikel/python',
                "Latih model regresi linear untuk menebak harga rumah dan pohon keputusan untuk mengklasifikasikan tumor, lalu ukur kinerjanya dan simpan modelnya agar bisa dipakai ulang.\n\n"
                .'Materi disusun berurutan sehingga sebaiknya dibaca dari materi pertama. Anda cukup memahami dasar Python serta cara membagi data latih dan data uji seperti yang dibahas di tutorial "Pengantar Machine Learning dengan Python".',
                [
                    'Regresi Linear',
                    'Klasifikasi dengan Decision Tree',
                    'Mengukur Kinerja Model',
                    'Menyimpan dan Memakai Ulang Model',
                ],
            ],
            [
                'Deep Learning',
                'Klasifikasi Gambar dengan CNN',
                'artikel/kecerdasan-buatan',
                "Ajari komputer mengenali jenis bunga dari foto dengan CNN, mulai dari memahami konvolusi dan menyiapkan foto per kelas hingga memanfaatkan MobileNetV2 yang sudah dilatih.\n\n"
                .'Materi disusun berurutan sehingga sebaiknya dibaca dari materi pertama. Anda cukup memahami dasar Python dan Keras seperti yang dibahas di tutorial "Pengantar Deep Learning dengan Keras".',
                [
                    'Mengenal Convolutional Neural Network',
                    'Menyiapkan Dataset Gambar',
                    'Membangun dan Melatih CNN',
                    'Transfer Learning dengan MobileNetV2',
                ],
            ],
        ];
    }
}
