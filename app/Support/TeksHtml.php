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

        // Akhir paragraf/butir/sub-judul/blok kode & <br> diberi spasi agar kata dari blok berbeda tidak menempel.
        $teks = strip_tags((string) preg_replace('#</(p|li|ul|ol|h2|h3|pre)>|<br\s*/?>#i', '$0 ', $html));

        return trim((string) preg_replace('/\s+/u', ' ', html_entity_decode($teks, ENT_QUOTES | ENT_HTML5, 'UTF-8')));
    }

    /**
     * Teks polos untuk ringkasan kartu: sub-judul (h2/h3) dibuang agar tidak menyambung dengan
     * kalimat paragraf sesudahnya, mis. "…kendalikan. Dipercaya calon pelanggan Pelanggan cenderung…".
     * Blok kode (isi materi tutorial) juga dibuang karena bukan kalimat.
     */
    public static function ringkasan(?string $html): string
    {
        return self::polos(preg_replace('#<(h2|h3|pre)\b[^>]*>.*?</\1>#is', ' ', (string) $html));
    }

    /**
     * Potong teks polos di akhir kata terdekat sebelum $maks karakter (spasi ikut dihitung) lalu beri "...",
     * agar kata terakhir tidak terpenggal (pilihan pemilik 2026-10-11). Tanda baca di ujung potongan dibuang
     * supaya tidak menjadi ",..." atau "....". Satu kata yang lebih panjang dari $maks tetap dipotong paksa.
     */
    public static function potongDiAkhirKata(string $teks, int $maks): string
    {
        if (mb_strlen($teks) <= $maks) {
            return $teks;
        }

        // Ambil satu karakter lebih: bila karakter ke-($maks + 1) spasi, potongan $maks karakter sudah utuh per kata.
        $potongan = mb_substr($teks, 0, $maks + 1);
        $spasiTerakhir = mb_strrpos($potongan, ' ');
        $potongan = $spasiTerakhir === false ? mb_substr($teks, 0, $maks) : mb_substr($potongan, 0, $spasiTerakhir);

        return rtrim($potongan, ' ,.;:-').'...';
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
