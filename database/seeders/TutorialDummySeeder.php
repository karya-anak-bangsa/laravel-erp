<?php

namespace Database\Seeders;

use App\Enums\CompanyProfile\JenisProgram;
use App\Enums\CompanyProfile\StatusPublikasi;
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
     * Tutorial contoh (pilihan pemilik 2026-10-20): "Tutorial Laravel 13" berisi 8 materi bersambung yang membangun satu
     * aplikasi data buku. Bagian dari CompanyProfileSeeder (lokal & produksi).
     * Isi tiap materi ada di database/seeders/data/tutorial/<slug judul tutorial>/<slug judul materi>.html. Sampulnya
     * memakai ulang ilustrasi artikel (database/seeders/berkas/<berkas>.webp) agar tidak menambah berkas biner baru.
     */
    public function run(TutorialService $tutorialService, MateriTutorialService $materiTutorialService, HtmlSanitizerService $sanitizer): void
    {
        foreach ($this->daftarTutorial() as [$jenis, $judul, $berkasSampul, $deskripsi, $daftarMateri]) {
            // Berdasarkan judul agar aman dijalankan ulang dan tidak menyalin gambar dua kali.
            if (Tutorial::withTrashed()->where('judul', $judul)->exists()) {
                continue;
            }

            $tutorial = Tutorial::create([
                'jenis_program' => $jenis,
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
     * @return list<array{JenisProgram, string, string, string, list<string>}> [jenis program, judul, berkas sampul,
     *                                                                         deskripsi teks polos, judul materi urut]
     */
    private function daftarTutorial(): array
    {
        return [
            [
                JenisProgram::PelatihanIt,
                'Tutorial Laravel 13',
                'artikel/laravel',
                "Seri delapan materi ini mengajak Anda membangun aplikasi data buku sederhana dengan Laravel 13, mulai dari mengenal fitur barunya dan memasang proyek hingga menampilkan, menambah, mengubah, dan menghapus data di basis data MySQL.\n\n"
                .'Materi disusun berurutan dan saling terhubung sehingga sebaiknya dibaca dari materi pertama. Anda cukup memahami dasar PHP dan HTML. Contoh perintahnya memakai Laragon di Windows, tetapi langkahnya juga berlaku di sistem operasi lain.',
                [
                    'Fitur-Fitur Baru di Laravel 13',
                    'Instalasi dan Konfigurasi Laravel 13',
                    'Routing, Controller, dan Blade',
                    'Migrasi dan Model Eloquent',
                    'Menampilkan Data dari Basis Data',
                    'Menambah Data dengan Formulir dan Validasi',
                    'Mengubah Data di Basis Data',
                    'Menghapus Data dari Basis Data',
                ],
            ],
        ];
    }
}
