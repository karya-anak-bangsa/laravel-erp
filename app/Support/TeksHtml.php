<?php

namespace App\Support;

class TeksHtml
{
    /**
     * Teks yang terlihat dari konten HTML editor: tag dibuang, entitas diurai, dan spasi
     * dirapatkan. Dipakai untuk ringkasan di tabel dan batas panjang isian, agar markup
     * format tidak ikut terhitung.
     */
    public static function polos(?string $html): string
    {
        if ($html === null || $html === '') {
            return '';
        }

        // Akhir paragraf/butir/sub-judul & <br> diberi spasi agar kata dari blok berbeda tidak menempel.
        $teks = strip_tags((string) preg_replace('#</(p|li|ul|ol|h2|h3)>|<br\s*/?>#i', '$0 ', $html));

        return trim((string) preg_replace('/\s+/u', ' ', html_entity_decode($teks, ENT_QUOTES | ENT_HTML5, 'UTF-8')));
    }

    /**
     * Teks polos (mis. data lama sebelum memakai editor) menjadi paragraf HTML yang aman:
     * baris kosong memisahkan paragraf, baris baru tunggal menjadi <br>.
     */
    public static function dariTeksPolos(?string $teks): ?string
    {
        if ($teks === null || trim($teks) === '') {
            return $teks;
        }

        $paragraf = preg_split('/\R\s*\R/u', trim($teks)) ?: [];

        return collect($paragraf)
            ->map(fn (string $isi) => '<p>'.preg_replace('/\R/u', '<br>', e(trim($isi))).'</p>')
            ->implode('');
    }
}
