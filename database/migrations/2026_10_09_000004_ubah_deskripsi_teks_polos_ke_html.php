<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

/**
 * Kolom deskripsi yang tampil di frontend kini diisi editor WYSIWYG (HTML tersanitasi).
 * Data lama berupa teks polos diubah menjadi paragraf HTML agar tampil sama dan aman
 * dirender dengan {!! !!}. Termasuk baris yang terhapus (soft delete).
 */
return new class extends Migration
{
    /** @var array<string, array{string, list<string>}> tabel => [primary key, kolom] */
    private array $kolom = [
        'tb_hero' => ['id_hero', ['deskripsi']],
        'tb_layanan' => ['id_layanan', ['deskripsi', 'keterangan']],
        'tb_portofolio' => ['id_portofolio', ['deskripsi']],
    ];

    public function up(): void
    {
        $this->ubah(function (string $teks): string {
            // Sudah HTML (diawali tag) tidak diubah lagi.
            if (str_starts_with(ltrim($teks), '<')) {
                return $teks;
            }

            // Baris kosong memisahkan paragraf; baris baru tunggal menjadi <br>.
            $paragraf = preg_split('/\R\s*\R/u', trim($teks)) ?: [];

            return implode('', array_map(
                fn (string $isi) => '<p>'.preg_replace('/\R/u', '<br>', htmlspecialchars(trim($isi), ENT_QUOTES, 'UTF-8')).'</p>',
                $paragraf,
            ));
        });
    }

    public function down(): void
    {
        // Format (tebal, daftar, tautan) hilang; teks dan pemisah paragrafnya dipertahankan.
        $this->ubah(function (string $html): string {
            $teks = preg_replace(['#</p>\s*<p>#i', '#<br\s*/?>#i', '#</li>#i'], ["\n\n", "\n", "\n"], $html);

            return trim(html_entity_decode(strip_tags((string) $teks), ENT_QUOTES | ENT_HTML5, 'UTF-8'));
        });
    }

    /**
     * @param  Closure(string): string  $ubah
     */
    private function ubah(Closure $ubah): void
    {
        foreach ($this->kolom as $tabel => [$kunci, $daftarKolom]) {
            DB::table($tabel)->orderBy($kunci)->chunkById(100, function ($baris) use ($tabel, $kunci, $daftarKolom, $ubah) {
                foreach ($baris as $data) {
                    $baru = [];

                    foreach ($daftarKolom as $kolom) {
                        $nilai = $data->{$kolom};

                        if (is_string($nilai) && trim($nilai) !== '') {
                            $baru[$kolom] = $ubah($nilai);
                        }
                    }

                    if ($baru !== []) {
                        DB::table($tabel)->where($kunci, $data->{$kunci})->update($baru);
                    }
                }
            }, $kunci);
        }
    }
};
