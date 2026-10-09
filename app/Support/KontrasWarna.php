<?php

namespace App\Support;

use InvalidArgumentException;

/**
 * Turunan keterbacaan warna template (WCAG 2.x). Port fungsi prototipe tema-ganda.html agar hasilnya
 * identik: teks berwarna digelapkan bertahap sampai kontras ≥ 4,5:1 di atas putih, dan warna teks di atas
 * tombol aksen dipilih putih atau gelap. Dihitung saat render, tidak disimpan.
 */
class KontrasWarna
{
    public const PUTIH = '#ffffff';

    public const GELAP = '#0f172a';

    /**
     * Hex #rrggbb huruf kecil. Selain format itu ditolak, sehingga hasilnya aman ditulis ke <style>.
     */
    public static function normalisasi(string $hex): string
    {
        if (preg_match('/^#[0-9a-f]{6}$/i', $hex) !== 1) {
            throw new InvalidArgumentException("Warna [{$hex}] harus berformat #rrggbb.");
        }

        return strtolower($hex);
    }

    public static function luminansi(string $hex): float
    {
        [$r, $g, $b] = array_map(function (int $v): float {
            $v /= 255;

            return $v <= 0.04045 ? $v / 12.92 : (($v + 0.055) / 1.055) ** 2.4;
        }, self::keRgb(self::normalisasi($hex)));

        return 0.2126 * $r + 0.7152 * $g + 0.0722 * $b;
    }

    public static function rasio(string $a, string $b): float
    {
        $terang = max(self::luminansi($a), self::luminansi($b));
        $gelap = min(self::luminansi($a), self::luminansi($b));

        return ($terang + 0.05) / ($gelap + 0.05);
    }

    /**
     * Warna teks yang terbaca di atas latar putih: RGB dikali 0,94 per langkah (maksimal 40 langkah).
     * Pembulatan hanya saat menulis hex, sama seperti prototipe.
     */
    public static function pastikanKontras(string $hex, float $minimum = 4.5): string
    {
        $hasil = self::normalisasi($hex);
        $rgb = array_map(fn (int $v): float => (float) $v, self::keRgb($hasil));

        for ($i = 0; $i < 40 && self::rasio($hasil, self::PUTIH) < $minimum; $i++) {
            $rgb = array_map(fn (float $v): float => $v * 0.94, $rgb);
            $hasil = self::keHex($rgb);
        }

        return $hasil;
    }

    /**
     * Warna teks di atas latar $hex (mis. tombol aksen): putih atau gelap, mana yang lebih kontras.
     */
    public static function teksDiAtas(string $hex): string
    {
        return self::rasio($hex, self::PUTIH) >= self::rasio($hex, self::GELAP) ? self::PUTIH : self::GELAP;
    }

    /**
     * @return array{int, int, int}
     */
    private static function keRgb(string $hex): array
    {
        return [(int) hexdec(substr($hex, 1, 2)), (int) hexdec(substr($hex, 3, 2)), (int) hexdec(substr($hex, 5, 2))];
    }

    /**
     * @param  list<float>  $rgb
     */
    private static function keHex(array $rgb): string
    {
        return '#'.implode('', array_map(fn (float $v): string => str_pad(dechex((int) round($v)), 2, '0', STR_PAD_LEFT), $rgb));
    }
}
